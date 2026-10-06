<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'all');

        $query = Role::withCount('users')
            ->when($tab === 'system', fn ($q) => $q->where('is_system', true))
            ->when($tab === 'custom', fn ($q) => $q->where('is_system', false))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $v = $request->q;
                $w->where('name', 'like', "%$v%")
                  ->orWhere('slug', 'like', "%$v%")
                  ->orWhere('description', 'like', "%$v%");
            }));

        $totalCount = Role::count();
        $systemCount = Role::where('is_system', true)->count();
        $customCount = Role::where('is_system', false)->count();

        $roles = $query->orderByDesc('is_system')->orderBy('name')->paginate(10)->withQueryString();

        $roleSuggestions = Role::select('id', 'name', 'slug', 'description', 'is_system')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get()
            ->map(function ($r) {
                return [
                    'code' => $r->slug,
                    'name' => $r->name,
                    'sub' => $r->description ?? ($r->is_system ? 'System Core Role' : 'Custom Operational Role'),
                    'value' => $r->name,
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
        return view('roles.form', ['role' => new Role(['is_active' => true, 'is_system' => false])]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:50|unique:roles,slug',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug'], '_') : Str::slug($validated['name'], '_');
        
        // Ensure unique slug
        $baseSlug = $slug;
        $counter = 1;
        while (Role::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}_{$counter}";
            $counter++;
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_system' => false,
            'is_active' => $request->boolean('is_active'),
        ]);

        \App\Models\AuditLog::record('create', 'roles', "Created access role: {$role->name} ({$role->slug})", [
            'slug' => $role->slug,
        ]);

        return redirect()->route('roles.index')->with('success', 'User role created successfully.');
    }

    public function edit(Role $role)
    {
        return view('roles.form', ['role' => $role]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => ['nullable', 'string', 'max:50', Rule::unique('roles', 'slug')->ignore($role->id)],
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $role->slug === 'superadmin' ? true : $request->boolean('is_active'),
        ];

        // System roles retain their fixed identifier
        if (!$role->is_system && !empty($validated['slug'])) {
            $data['slug'] = Str::slug($validated['slug'], '_');
        }

        $role->update($data);

        \App\Models\AuditLog::record('update', 'roles', "Updated role configuration: {$role->name}", [
            'changes' => $role->getChanges(),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'System default roles ('.$role->name.') cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete role "'.$role->name.'" because users are currently assigned to this role.');
        }

        $name = $role->name;
        $slug = $role->slug;
        $role->delete();

        \App\Models\AuditLog::record('delete', 'roles', "Deleted custom role: {$name} ({$slug})");

        return redirect()->route('roles.index')->with('success', 'Role removed successfully.');
    }

    public function toggle(Role $role)
    {
        if ($role->slug === 'superadmin') {
            return back()->with('error', 'Super Admin role cannot be deactivated.');
        }

        $role->update(['is_active' => ! $role->is_active]);
        $status = $role->is_active ? 'activated' : 'deactivated';

        \App\Models\AuditLog::record('toggle', 'roles', "Toggled role status: {$role->name} is now {$status}");

        return back()->with('success', "Role {$role->name} has been {$status}.");
    }
}
