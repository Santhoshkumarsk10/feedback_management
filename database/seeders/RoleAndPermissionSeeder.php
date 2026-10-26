<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Clean all old users, roles, and permissions
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        Permission::query()->delete();
        Role::query()->delete();
        User::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 3. Define the 14 Modules and their 5 permissions (view, create, edit, delete, export)
        $modules = [
            'dashboard' => 'Dashboard',
            'plantmaster' => 'Plant Master',
            'feedbackform' => 'Feedback Form',
            'plantvisitors' => 'Visitors',
            'companyprofile' => 'Company Profile',
            'bannercms' => 'Banner CMS',
            'feedbacklogs' => 'Feedback Logs',
            'reportsexports' => 'Reports & exports',
            'auditlogs' => 'Audit Logs',
            'userdirectory' => 'User Directory',
            'rolemaster' => 'Role Master',
            'permissionmaster' => 'Permission Master',
            'userprofile' => 'User Profile',
            'tabledatacleanup' => 'Table Data Cleanup',
        ];

        $actions = ['view', 'create', 'edit', 'delete', 'export'];

        foreach ($modules as $slug => $label) {
            foreach ($actions as $action) {
                $permKey = "{$slug}.{$action}";
                $displayAction = ucfirst($action);
                Permission::create([
                    'name' => $permKey,
                    'guard_name' => 'web',
                    'display_name' => "{$label} — {$displayAction}",
                    'module' => $label,
                    'description' => "Grants {$action} access on {$label} module",
                ]);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 4. Permission Mappings for Superadmin, Admin, and Coordinator based on Reference Matrices
        $superadminPermissions = [
            // Dashboard: View, Export
            'dashboard.view', 'dashboard.export',
            // Plant Master: View, Create, Edit, Delete, Export
            'plantmaster.view', 'plantmaster.create', 'plantmaster.edit', 'plantmaster.delete', 'plantmaster.export',
            // Feedback Form: View, Create, Edit, Delete
            'feedbackform.view', 'feedbackform.create', 'feedbackform.edit', 'feedbackform.delete',
            // Visitors: View, Create, Edit, Delete, Export
            'plantvisitors.view', 'plantvisitors.create', 'plantvisitors.edit', 'plantvisitors.delete', 'plantvisitors.export',
            // Company Profile: View, Edit
            'companyprofile.view', 'companyprofile.edit',
            // Banner CMS: View, Create, Edit
            'bannercms.view', 'bannercms.create', 'bannercms.edit',
            // Feedback Logs: View, Delete, Export
            'feedbacklogs.view', 'feedbacklogs.delete', 'feedbacklogs.export',
            // Reports & exports: View, Export
            'reportsexports.view', 'reportsexports.export',
            // Audit Logs: View, Export
            'auditlogs.view', 'auditlogs.export',
            // User Directory: View, Create, Edit, Delete, Export
            'userdirectory.view', 'userdirectory.create', 'userdirectory.edit', 'userdirectory.delete', 'userdirectory.export',
            // Role Master: View, Create, Edit, Delete
            'rolemaster.view', 'rolemaster.create', 'rolemaster.edit', 'rolemaster.delete',
            // Permission Master: View, Edit
            'permissionmaster.view', 'permissionmaster.edit',
            // User Profile: View, Edit
            'userprofile.view', 'userprofile.edit',
            // Table Data Cleanup: View, Delete
            'tabledatacleanup.view', 'tabledatacleanup.delete',
        ];

        $adminPermissions = [
            // Dashboard: View, Export
            'dashboard.view', 'dashboard.export',
            // Plant Master: View, Create, Edit, Export
            'plantmaster.view', 'plantmaster.create', 'plantmaster.edit', 'plantmaster.export',
            // Feedback Form: View, Create, Edit
            'feedbackform.view', 'feedbackform.create', 'feedbackform.edit',
            // Visitors: View, Create, Edit, Export
            'plantvisitors.view', 'plantvisitors.create', 'plantvisitors.edit', 'plantvisitors.export',
            // Company Profile: View, Edit
            'companyprofile.view', 'companyprofile.edit',
            // Banner CMS: View, Create, Edit, Delete
            'bannercms.view', 'bannercms.create', 'bannercms.edit', 'bannercms.delete',
            // Feedback Logs: View, Export
            'feedbacklogs.view', 'feedbacklogs.export',
            // Reports & exports: View, Export
            'reportsexports.view', 'reportsexports.export',
            // Audit Logs: View, Export
            'auditlogs.view', 'auditlogs.export',
            // User Directory: View, Create, Edit, Export
            'userdirectory.view', 'userdirectory.create', 'userdirectory.edit', 'userdirectory.export',
            // Role Master: View
            'rolemaster.view',
            // Permission Master: View
            'permissionmaster.view',
            // User Profile: View, Edit
            'userprofile.view', 'userprofile.edit',
        ];

        $coordinatorPermissions = [
            // Dashboard: View
            'dashboard.view',
            // Plant Master: View
            'plantmaster.view',
            // Feedback Form: View, Create
            'feedbackform.view', 'feedbackform.create',
            // Visitors: View
            'plantvisitors.view',
            // Company Profile: View
            'companyprofile.view',
            // Reports & exports: View
            'reportsexports.view',
            // User Profile: View, Edit
            'userprofile.view', 'userprofile.edit',
        ];

        // 5. Create the 3 Roles (superadmin, admin, coordinator)
        $roleDefinitions = [
            'superadmin' => [
                'display_name' => 'Superadmin',
                'description' => 'Full administrative control across all system operational modules and configurations',
                'is_system' => true,
                'permissions' => $superadminPermissions,
            ],
            'admin' => [
                'display_name' => 'Admin',
                'description' => 'Administrative role managing plant facilities, visitor operations, and reporting',
                'is_system' => true,
                'permissions' => $adminPermissions,
            ],
            'coordinator' => [
                'display_name' => 'Coordinator',
                'description' => 'Operations coordinator facilitating visitor experiences, evaluation forms, and analytics',
                'is_system' => true,
                'permissions' => $coordinatorPermissions,
            ],
        ];

        $createdRoles = [];
        foreach ($roleDefinitions as $roleKey => $meta) {
            $role = Role::create([
                'name' => $roleKey,
                'slug' => $roleKey,
                'guard_name' => 'web',
                'display_name' => $meta['display_name'],
                'description' => $meta['description'],
                'is_system' => $meta['is_system'],
                'is_active' => true,
            ]);

            $role->syncPermissions($meta['permissions']);
            $createdRoles[$roleKey] = $role;
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 6. Create Users and Assign Roles
        // 1) Superadmin User
        $superUser = User::create([
            'name' => 'Superadmin',
            'email' => 'superadmin@plant.test',
            'mobile' => '9844000001',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'role_id' => $createdRoles['superadmin']->id,
            'department' => 'Management',
            'is_active' => true,
        ]);
        $superUser->syncRoles(['superadmin']);

        // 2) Admin User
        $adminUser = User::create([
            'name' => 'Admin',
            'email' => 'admin@plant.test',
            'mobile' => '9844000002',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'role_id' => $createdRoles['admin']->id,
            'department' => 'Plant Operations',
            'is_active' => true,
        ]);
        $adminUser->syncRoles(['admin']);

        // 3) Coordinator User
        $coordUser = User::create([
            'name' => 'Coordinator',
            'email' => 'coordinator@plant.test',
            'mobile' => '9844000003',
            'password' => Hash::make('password'),
            'role' => 'coordinator',
            'role_id' => $createdRoles['coordinator']->id,
            'department' => 'Customer Relations & Tours',
            'is_active' => true,
        ]);
        $coordUser->syncRoles(['coordinator']);

        // Re-link existing operational records if any exist in the database
        DB::table('visits')->update(['organizer_id' => $coordUser->id]);
        DB::table('feedbacks')->update(['organizer_id' => $coordUser->id]);
        DB::table('audit_logs')->whereNotNull('user_id')->update(['user_id' => $superUser->id]);
    }
}
