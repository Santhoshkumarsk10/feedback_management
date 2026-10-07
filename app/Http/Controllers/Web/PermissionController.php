<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'module' => ['nullable', 'string', 'max:50'],
        ]);

        $moduleFilter = $request->input('module', 'all');

        $query = Permission::with('roles')
            ->when($moduleFilter !== 'all', fn ($q) => $q->where('module', $moduleFilter))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $v = $request->q;
                $w->where('name', 'like', "%$v%")
                  ->orWhere('display_name', 'like', "%$v%")
                  ->orWhere('module', 'like', "%$v%")
                  ->orWhere('description', 'like', "%$v%");
            }));

        $totalCount = Permission::count();
        $allModules = Permission::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->all();

        $permissions = $query->orderBy('module')->orderBy('name')->paginate(15)->withQueryString();

        $permissionSuggestions = Permission::select('id', 'name', 'display_name', 'module', 'description')
            ->orderBy('module')
            ->get()
            ->map(function ($p) {
                return [
                    'code' => $p->name,
                    'name' => $p->display_name ?: $p->name,
                    'sub' => $p->module . ($p->description ? ' • ' . $p->description : ''),
                    'value' => $p->display_name ?: $p->name,
                ];
            });

        return view('permissions.index', [
            'permissions' => $permissions,
            'allModules' => $allModules,
            'currentModule' => $moduleFilter,
            'totalCount' => $totalCount,
            'permissionSuggestions' => $permissionSuggestions,
        ]);
    }

    /**
     * Show form for creating a new permission.
     */
    public function create()
    {
        $existingModules = Permission::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->all();

        $roles = Role::where('is_active', true)->orderBy('name')->get();

        return view('permissions.form', [
            'permission' => new Permission(['module' => 'System Masters']),
            'existingModules' => $existingModules,
            'roles' => $roles,
            'assignedRoleIds' => [],
        ]);
    }

    /**
     * Store a new permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-z0-9\-]+$/', 'unique:permissions,name'],
            'display_name' => ['required', 'string', 'min:2', 'max:80', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'module' => ['required', 'string', 'min:2', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ], [
            'name.regex' => 'Permission Key must only contain lowercase letters, numbers, and hyphens (e.g. export-reports).',
            'name.unique' => 'This permission key is already registered.',
        ]);

        $permission = Permission::create([
            'name' => Str::slug($validated['name'], '-'),
            'display_name' => $validated['display_name'],
            'module' => trim($validated['module']),
            'description' => $validated['description'] ?? null,
            'guard_name' => 'web',
        ]);

        if ($request->filled('roles')) {
            $selectedRoles = Role::whereIn('id', $request->input('roles'))->get();
            foreach ($selectedRoles as $r) {
                $r->givePermissionTo($permission);
            }
        }

        AuditLog::record('create', 'permissions', "Registered system permission: {$permission->display_name} ({$permission->name})", [
            'module' => $permission->module,
            'key' => $permission->name,
        ]);

        return redirect()->route('permissions.index')->with('success', "Permission '{$permission->display_name}' created successfully.");
    }

    /**
     * Show form for editing a permission.
     */
    public function edit(Permission $permission)
    {
        $existingModules = Permission::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->all();

        $roles = Role::where('is_active', true)->orderBy('name')->get();
        $assignedRoleIds = $permission->roles->pluck('id')->toArray();

        return view('permissions.form', [
            'permission' => $permission,
            'existingModules' => $existingModules,
            'roles' => $roles,
            'assignedRoleIds' => $assignedRoleIds,
        ]);
    }

    /**
     * Update an existing permission.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'min:2', 'max:80', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'module' => ['required', 'string', 'min:2', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $permission->update([
            'display_name' => $validated['display_name'],
            'module' => trim($validated['module']),
            'description' => $validated['description'] ?? null,
        ]);

        // Sync roles assigned to this permission
        $selectedRoleIds = $request->input('roles', []);
        $allRoles = Role::all();
        foreach ($allRoles as $r) {
            if ($r->name === 'superadmin') {
                continue; // Superadmin has all permissions
            }
            if (in_array($r->id, $selectedRoleIds)) {
                $r->givePermissionTo($permission);
            } else {
                $r->revokePermissionTo($permission);
            }
        }

        AuditLog::record('update', 'permissions', "Updated permission metadata: {$permission->name}", [
            'changes' => $permission->getChanges(),
        ]);

        return redirect()->route('permissions.index')->with('success', "Permission '{$permission->display_name}' updated successfully.");
    }

    /**
     * Delete a permission.
     */
    public function destroy(Permission $permission)
    {
        $coreSystemPerms = [
            'access-web-panel',
            'access-mobile-app',
            'manage-users',
            'manage-roles',
            'manage-shifts',
            'manage-plants',
            'manage-questions',
            'view-visits',
            'manage-visits',
            'view-feedbacks',
            'submit-feedback',
            'view-reports',
            'export-reports',
        ];

        if (in_array($permission->name, $coreSystemPerms, true)) {
            return back()->with('error', "Core system permission '{$permission->name}' cannot be deleted as platform features depend on it.");
        }

        $name = $permission->display_name ?: $permission->name;
        $key = $permission->name;

        // Detach roles & delete
        $permission->roles()->detach();
        $permission->delete();

        AuditLog::record('delete', 'permissions', "Deleted custom permission: {$name} ({$key})");

        return redirect()->route('permissions.index')->with('success', "Permission '{$name}' removed successfully.");
    }
}
