<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
        ]);

        $tab = $request->input('tab', 'all');

        $query = Role::withCount(['assignedUsers', 'permissions'])
            ->with('permissions')
            ->when($tab === 'system', fn ($q) => $q->where('is_system', true))
            ->when($tab === 'custom', fn ($q) => $q->where('is_system', false))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $v = $request->q;
                $w->where('name', 'like', "%$v%")
                  ->orWhere('display_name', 'like', "%$v%")
                  ->orWhere('slug', 'like', "%$v%")
                  ->orWhere('description', 'like', "%$v%");
            }));

        $totalCount = Role::count();
        $systemCount = Role::where('is_system', true)->count();
        $customCount = Role::where('is_system', false)->count();

        $roles = $query->orderByDesc('is_system')->orderBy('name')->paginate(10)->withQueryString();

        $roleSuggestions = Role::select('id', 'name', 'display_name', 'slug', 'description', 'is_system')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get()
            ->map(function ($r) {
                return [
                    'code' => $r->slug ?: $r->name,
                    'name' => $r->display_name ?: $r->name,
                    'sub' => $r->description ?? ($r->is_system ? 'System Core Role' : 'Custom Operational Role'),
                    'value' => $r->display_name ?: $r->name,
                ];
            });

        return view('roles.index', [
            'roles' => $roles,
            'tabCounts' => [
                'all' => $totalCount,
                'system' => $systemCount,
                'custom' => $customCount,
            ],
            'currentTab' => $tab,
            'roleSuggestions' => $roleSuggestions,
        ]);
    }

    public function create()
    {
        $groupedPermissions = Permission::orderBy('module')->orderBy('id')->get()->groupBy('module');

        return view('roles.form', [
            'role' => new Role(['is_active' => true, 'is_system' => false]),
            'groupedPermissions' => $groupedPermissions,
            'rolePermissions' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:70', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'slug' => ['nullable', 'string', 'min:2', 'max:50', 'regex:/^[a-zA-Z0-9_\-]+$/', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'min:2', 'max:500'],
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.required' => 'Role Title is required.',
            'name.min' => 'Role Title must be at least 2 characters.',
            'name.regex' => 'Role Title contains invalid characters.',
            'slug.regex' => 'Role Identifier may only contain letters, numbers, hyphens, and underscores.',
            'slug.unique' => 'This Role Identifier is already in use.',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug'], '_') : Str::slug($validated['name'], '_');
        
        // Ensure unique slug
        $baseSlug = $slug;
        $counter = 1;
        while (Role::where('name', $slug)->exists()) {
            $slug = "{$baseSlug}_{$counter}";
            $counter++;
        }

        $role = Role::create([
            'name' => $slug,
            'display_name' => $validated['name'],
            'slug' => $slug,
            'guard_name' => 'web',
            'description' => $validated['description'] ?? null,
            'is_system' => false,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions', []));
        }

        AuditLog::record('create', 'roles', "Created access role: {$role->display_name} ({$role->name}) with " . count($request->input('permissions', [])) . " permissions", [
            'slug' => $role->name,
            'permissions' => $request->input('permissions', []),
        ]);

        return redirect()->route('roles.index')->with('success', "Role '{$role->display_name}' created successfully.");
    }

    public function edit(Role $role)
    {
        $groupedPermissions = Permission::orderBy('module')->orderBy('id')->get()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.form', [
            'role' => $role,
            'groupedPermissions' => $groupedPermissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:70', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'slug' => [
                'nullable',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-Z0-9_\-]+$/',
                Rule::unique('roles', 'name')->ignore($role->id)
            ],
            'description' => ['nullable', 'string', 'min:2', 'max:500'],
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.required' => 'Role Title is required.',
            'name.min' => 'Role Title must be at least 2 characters.',
            'name.regex' => 'Role Title contains invalid characters.',
        ]);

        $data = [
            'display_name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $role->name === 'superadmin' ? true : $request->boolean('is_active'),
        ];

        if (!$role->is_system && !empty($validated['slug'])) {
            $data['name'] = Str::slug($validated['slug'], '_');
            $data['slug'] = $data['name'];
        }

        $role->update($data);

        // Update permissions for custom roles (or if superadmin)
        if ($role->name !== 'superadmin') {
            $role->syncPermissions($request->input('permissions', []));
        }

        AuditLog::record('update', 'roles', "Updated access role: {$role->display_name} ({$role->name})", [
            'changes' => $role->getChanges(),
            'permissions_count' => count($request->input('permissions', [])),
        ]);

        return redirect()->route('roles.index')->with('success', "Role '{$role->display_name}' updated successfully.");
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'Core system roles cannot be deleted.');
        }

        if ($role->assignedUsers()->exists()) {
            return back()->with('error', "Cannot delete role '{$role->display_name}' because users are currently assigned to it.");
        }

        $name = $role->display_name ?: $role->name;
        $slug = $role->name;

        // Revoke all permissions and delete
        $role->syncPermissions([]);
        $role->delete();

        AuditLog::record('delete', 'roles', "Deleted custom role: {$name} ({$slug})");

        return redirect()->route('roles.index')->with('success', "Role '{$name}' removed successfully.");
    }

    public function toggle(Role $role)
    {
        if ($role->name === 'superadmin') {
            return back()->with('error', 'Super Administrator role cannot be deactivated.');
        }

        $role->update(['is_active' => !$role->is_active]);
        $status = $role->is_active ? 'activated' : 'deactivated';

        AuditLog::record('toggle', 'roles', "Toggled role status: {$role->display_name} is now {$status}");

        return back()->with('success', "Role '{$role->display_name}' has been {$status}.");
    }
}
