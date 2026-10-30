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
            'per_page' => 'nullable|integer',
        ]);

        $moduleFilter = $request->input('module');
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $query = Permission::with('roles')
            ->when(!empty($moduleFilter) && $moduleFilter !== 'all', fn ($q) => $q->where('module', $moduleFilter))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $v = $request->q;
                $w->where('name', 'like', "%$v%")
                  ->orWhere('display_name', 'like', "%$v%")
                  ->orWhere('module', 'like', "%$v%")
                  ->orWhere('description', 'like', "%$v%");
            }));

        $totalCount = min(Permission::count(), 1000);
        $allModules = Permission::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->all();

        $moduleCounts = Permission::selectRaw('module, count(*) as count')
            ->groupBy('module')
            ->pluck('count', 'module')
            ->all();

        $permissions = $query->orderBy('module')->orderBy('name')->paginate(10)->withQueryString();

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
            'moduleCounts' => $moduleCounts,
            'currentModule' => (!empty($moduleFilter) && $moduleFilter !== 'all') ? $moduleFilter : '',
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

        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
        $roles = Role::where('is_active', true)
            ->when(!$isSuperAdmin, fn ($q) => $q->where('name', '!=', 'superadmin'))
            ->orderBy('name')
            ->get();

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
        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-z0-9\-\.]+$/', 'unique:permissions,name'],
            'display_name' => ['required', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'module' => ['required', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'description' => ['nullable', 'string', 'max:150'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ], [
            'name.regex' => 'Permission Key may only contain lowercase letters, numbers, hyphens, and dots (e.g. plantmaster.view).',
            'name.unique' => 'This permission key is already registered.',
            'module.regex' => 'Module Name contains invalid characters.',
            'display_name.regex' => 'Display Title contains invalid characters.',
        ]);

        $permission = Permission::create([
            'name' => strtolower(trim($validated['name'])),
            'display_name' => $validated['display_name'],
            'module' => trim($validated['module']),
            'description' => $validated['description'] ?? null,
            'guard_name' => 'web',
        ]);

        if ($request->filled('roles')) {
            $selectedRoles = Role::whereIn('id', $request->input('roles'))
                ->when(!$isSuperAdmin, fn ($q) => $q->whereNotIn('name', ['superadmin', 'admin']))
                ->get();
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

        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
        $roles = Role::where('is_active', true)
            ->when(!$isSuperAdmin, fn ($q) => $q->where('name', '!=', 'superadmin'))
            ->orderBy('name')
            ->get();
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
        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'module' => ['required', 'string', 'min:2', 'max:100', 'regex:~^[\p{L}\p{N}\s\-–—_&/,\.()\'’]+$~u'],
            'description' => ['nullable', 'string', 'max:150'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ], [
            'display_name.regex' => 'Display Title contains invalid characters.',
            'module.regex' => 'Module Name contains invalid characters.',
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
            if (!$isSuperAdmin && $r->name === 'admin') {
                continue; // Admin cannot modify admin permissions
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
            'dashboard.view',
            'plantmaster.view',
            'feedbackform.view',
            'plantvisitors.view',
            'companyprofile.view',
            'bannercms.view',
            'feedbacklogs.view',
            'reportsexports.view',
            'auditlogs.view',
            'userdirectory.view',
            'rolemaster.view',
            'permissionmaster.view',
            'userprofile.view',
            'tabledatacleanup.view',
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
