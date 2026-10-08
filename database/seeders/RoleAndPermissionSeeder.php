<?php

namespace Database\Seeders;

use App\Models\Plant;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Permissions
        $permissions = [
            'access-web-panel',
            'access-mobile-app',
            'manage-users',
            'manage-roles',
            'manage-shifts',
            'manage-plants',
            'manage-questions',
            'manage-company',
            'manage-banners',
            'view-reports',
            'export-reports',
            'view-visits',
            'manage-visits',
            'view-feedbacks',
            'submit-feedback',
            'view-audit-logs',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Define Roles
        $roleDefinitions = [
            'superadmin' => [
                'display_name' => 'Super Admin',
                'description' => 'Full administrative access across all plants, system settings, and security audits',
                'is_system' => true,
                'permissions' => $permissions,
            ],
            'admin' => [
                'display_name' => 'Plant Admin',
                'description' => 'Plant-level administrator managing visitors, team members, shifts, and questionnaires',
                'is_system' => true,
                'permissions' => [
                    'access-web-panel',
                    'access-mobile-app',
                    'manage-users',
                    'manage-roles',
                    'manage-shifts',
                    'manage-plants',
                    'manage-questions',
                    'manage-company',
                    'manage-banners',
                    'view-reports',
                    'export-reports',
                    'view-visits',
                    'manage-visits',
                    'view-feedbacks',
                    'submit-feedback',
                    'view-audit-logs',
                ],
            ],
            'supervisor' => [
                'display_name' => 'Operations Supervisor',
                'description' => 'Supervises staff, audits plant visits, feedback logs, and performance analytics',
                'is_system' => true,
                'permissions' => [
                    'access-web-panel',
                    'view-reports',
                    'export-reports',
                    'view-visits',
                    'view-feedbacks',
                ],
            ],
            'staff' => [
                'display_name' => 'Shift Staff',
                'description' => 'Shift employee on duty assisting visitors, logging tours, and gathering reviews',
                'is_system' => true,
                'permissions' => [
                    'access-web-panel',
                    'access-mobile-app',
                    'view-visits',
                    'manage-visits',
                    'view-feedbacks',
                    'submit-feedback',
                ],
            ],
            'organizer' => [
                'display_name' => 'Tour Organizer',
                'description' => 'Legacy tour organizer role mapped to shift staff',
                'is_system' => true,
                'permissions' => [
                    'access-web-panel',
                    'access-mobile-app',
                    'view-visits',
                    'manage-visits',
                    'view-feedbacks',
                    'submit-feedback',
                ],
            ],
        ];

        foreach ($roleDefinitions as $roleKey => $meta) {
            $role = Role::where('slug', $roleKey)->first() ?? Role::where('name', $roleKey)->first();
            if (!$role) {
                $role = Role::create([
                    'name' => $roleKey,
                    'slug' => $roleKey,
                    'guard_name' => 'web',
                    'display_name' => $meta['display_name'],
                    'description' => $meta['description'],
                    'is_system' => $meta['is_system'],
                    'is_active' => true,
                ]);
            } else {
                $role->update([
                    'name' => $roleKey,
                    'slug' => $roleKey,
                    'guard_name' => 'web',
                    'display_name' => $meta['display_name'],
                    'description' => $meta['description'],
                    'is_system' => $meta['is_system'],
                    'is_active' => true,
                ]);
            }

            $role->syncPermissions($meta['permissions']);
        }

        // 3. Link Existing & Demo Users
        $defaultPlant = Plant::first();
        $p1 = Plant::where('code', 'PLANT-01')->first() ?? $defaultPlant;
        $p2 = Plant::where('code', 'PLANT-02')->first() ?? $defaultPlant;

        $roleSuper = Role::where('name', 'superadmin')->first();
        $roleAdmin = Role::where('name', 'admin')->first();
        $roleSupervisor = Role::where('name', 'supervisor')->first();
        $roleStaff = Role::where('name', 'staff')->first();

        // 1) Super Admin
        $super = User::updateOrCreate(
            ['email' => 'superadmin@plant.test'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'role_id' => $roleSuper?->id,
                'plant_id' => $p1?->id,
                'is_active' => true,
            ]
        );
        $super->syncRoles(['superadmin']);

        // 2) Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@plant.test'],
            [
                'name' => 'Plant Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'role_id' => $roleAdmin?->id,
                'plant_id' => $p1?->id,
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin']);

        // 3) Supervisor
        $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@plant.test'],
            [
                'name' => 'Plant Supervisor',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'role_id' => $roleSupervisor?->id,
                'plant_id' => $p1?->id,
                'department' => 'Quality & Operations',
                'is_active' => true,
            ]
        );
        $supervisor->syncRoles(['supervisor']);

        // 4) Staff
        $staffUser = User::updateOrCreate(
            ['email' => 'staff@plant.test'],
            [
                'name' => 'Shift Staff Engineer',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'role_id' => $roleStaff?->id,
                'plant_id' => $p2?->id,
                'department' => 'Machine Tools Assembly',
                'is_active' => true,
            ]
        );
        $staffUser->syncRoles(['staff']);

        // 5) Sync existing organizers to 'staff' Spatie role
        $existingStaff = User::whereIn('role', ['organizer', 'staff'])->get();
        foreach ($existingStaff as $u) {
            $u->update([
                'role_id' => $roleStaff?->id,
            ]);
            $u->syncRoles(['staff']);
        }
    }
}
