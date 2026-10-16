<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAndPermissionCrudTest extends TestCase
{
    private function getAdmin(): User
    {
        return User::where('role', 'superadmin')->first() 
            ?? User::where('role', 'admin')->first()
            ?? User::factory()->create(['role' => 'superadmin']);
    }

    public function test_roles_index_displays_permissions_count(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('roles.index'));
        $response->assertStatus(200);
        $response->assertSee('Defined System Roles');
        $response->assertSee('Permission Master');
    }

    public function test_create_role_stores_role_and_syncs_permissions(): void
    {
        $admin = $this->getAdmin();

        // Clean up test role if exists
        $existing = Role::where('name', 'test_custom_role')->first();
        if ($existing) {
            $existing->syncPermissions([]);
            $existing->delete();
        }

        $response = $this->actingAs($admin)->post(route('roles.store'), [
            'name' => 'Custom Shift Leader',
            'slug' => 'test_custom_role',
            'description' => 'Leader assisting visitors during specific production shifts',
            'is_active' => '1',
            'permissions' => ['plantvisitors.view', 'feedbackform.create', 'feedbacklogs.view'],
        ]);

        $response->assertRedirect(route('roles.index'));

        $role = Role::where('name', 'test_custom_role')->first();
        $this->assertNotNull($role);
        $this->assertEquals('Custom Shift Leader', $role->display_name);
        $this->assertTrue($role->hasPermissionTo('plantvisitors.view'));
        $this->assertTrue($role->hasPermissionTo('feedbackform.create'));
        $this->assertFalse($role->hasPermissionTo('userdirectory.create'));
    }

    public function test_edit_role_page_renders_with_permission_matrix(): void
    {
        $admin = $this->getAdmin();
        $role = Role::where('name', 'test_custom_role')->first() ?? Role::where('name', 'coordinator')->first();

        $response = $this->actingAs($admin)->get(route('roles.edit', $role));
        $response->assertStatus(200);
        $response->assertSee('Capability Permissions Matrix');
        $response->assertSee('plantvisitors.view');
    }

    public function test_update_role_modifies_assigned_permissions(): void
    {
        $admin = $this->getAdmin();
        $role = Role::where('name', 'test_custom_role')->first();
        $this->assertNotNull($role);

        $response = $this->actingAs($admin)->put(route('roles.update', $role), [
            'name' => 'Updated Shift Leader',
            'slug' => 'test_custom_role',
            'description' => 'Updated shift description',
            'is_active' => '1',
            'permissions' => ['plantvisitors.view', 'plantmaster.view'],
        ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertTrue($role->fresh()->hasPermissionTo('plantmaster.view'));
        $this->assertFalse($role->fresh()->hasPermissionTo('feedbackform.create'));
    }

    public function test_permissions_index_page_displays_all_capabilities(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('permissions.index'));
        $response->assertStatus(200);
        $response->assertSee('Capability Permissions Directory');
        $response->assertSee('dashboard.view');
        $response->assertSee('plantmaster.view');
    }

    public function test_create_and_store_new_custom_permission(): void
    {
        $admin = $this->getAdmin();

        // Clean up if exists
        Permission::where('name', 'custom.audit.export')->delete();

        $coordRole = Role::where('name', 'coordinator')->first();

        $response = $this->actingAs($admin)->post(route('permissions.store'), [
            'name' => 'custom.audit.export',
            'display_name' => 'Export Shift Audits',
            'module' => 'Audit Logs',
            'description' => 'Allows exporting historical shift logs and evaluation trails',
            'roles' => [$coordRole->id],
        ]);

        $response->assertRedirect(route('permissions.index'));

        $perm = Permission::where('name', 'custom.audit.export')->first();
        $this->assertNotNull($perm);
        $this->assertEquals('Export Shift Audits', $perm->display_name);
        $this->assertEquals('Audit Logs', $perm->module);
        $this->assertTrue($coordRole->fresh()->hasPermissionTo('custom.audit.export'));
    }

    public function test_core_permissions_protected_from_deletion(): void
    {
        $admin = $this->getAdmin();
        $corePerm = Permission::where('name', 'plantmaster.view')->firstOrFail();

        $response = $this->actingAs($admin)->delete(route('permissions.destroy', $corePerm));
        $response->assertSessionHas('error');

        $this->assertNotNull(Permission::where('name', 'plantmaster.view')->first());
    }

    public function test_custom_permission_can_be_deleted(): void
    {
        $admin = $this->getAdmin();
        $perm = Permission::where('name', 'custom.audit.export')->first();
        if ($perm) {
            $response = $this->actingAs($admin)->delete(route('permissions.destroy', $perm));
            $response->assertRedirect(route('permissions.index'));
            $this->assertNull(Permission::where('name', 'custom.audit.export')->first());
        }
    }

    public function test_coordinator_forbidden_from_permission_master(): void
    {
        $coordinator = User::where('role', 'coordinator')->first();

        $this->actingAs($coordinator)->get(route('permissions.index'))->assertStatus(403);
    }

    public function test_permissions_index_filters_by_module_dropdown(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('permissions.index', ['module' => 'Plant Master']));
        $response->assertStatus(200);
        $response->assertSee('plantmaster.view');
        $response->assertSee('plantmaster.create');
        $response->assertViewHas('permissions', function ($perms) {
            return $perms->count() === 5 && $perms->every(fn ($p) => $p->module === 'Plant Master');
        });
    }

    public function test_add_buttons_exist_for_roles_permissions_and_users(): void
    {
        $admin = $this->getAdmin();

        // 1. Roles Index contains Add Role button and create route works
        $roleIndexResponse = $this->actingAs($admin)->get(route('roles.index'));
        $roleIndexResponse->assertStatus(200);
        $roleIndexResponse->assertSee('Add Role');
        $roleIndexResponse->assertSee(route('roles.create'));
        $this->actingAs($admin)->get(route('roles.create'))->assertStatus(200);

        // 2. Permissions Index contains Add Permission button and create route works
        $permIndexResponse = $this->actingAs($admin)->get(route('permissions.index'));
        $permIndexResponse->assertStatus(200);
        $permIndexResponse->assertSee('Add Permission');
        $permIndexResponse->assertSee(route('permissions.create'));
        $this->actingAs($admin)->get(route('permissions.create'))->assertStatus(200);

        // 3. Users Index contains Add User button and create route works
        $userIndexResponse = $this->actingAs($admin)->get(route('users.index'));
        $userIndexResponse->assertStatus(200);
        $userIndexResponse->assertSee('Add User');
        $userIndexResponse->assertSee(route('users.create'));
        $this->actingAs($admin)->get(route('users.create'))->assertStatus(200);
    }

    public function test_global_search_returns_visitors_coordinators_feedbacks_or_empty(): void
    {
        $admin = $this->getAdmin();

        // 1. Search for coordinator
        $response = $this->actingAs($admin)->getJson(route('dashboard.global_search', ['q' => 'coordinator']));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'results' => ['visitors', 'feedbacks', 'coordinators'],
            'total',
        ]);
        $this->assertTrue(collect($response->json('results.coordinators'))->contains(fn ($c) => str_contains(strtolower($c['role']), 'coordinator')));

        // 2. Search for unrelated random string returns 0 total and empty arrays
        $emptyResponse = $this->actingAs($admin)->getJson(route('dashboard.global_search', ['q' => 'random_unrelated_xyz_9999']));
        $emptyResponse->assertStatus(200);
        $this->assertEquals(0, $emptyResponse->json('total'));
        $this->assertEmpty($emptyResponse->json('results.visitors'));
        $this->assertEmpty($emptyResponse->json('results.coordinators'));
        $this->assertEmpty($emptyResponse->json('results.feedbacks'));
    }

    public function test_all_demo_users_can_log_in_to_web_panel(): void
    {
        $accounts = [
            'superadmin@plant.test',
            'admin@plant.test',
            'coordinator@plant.test',
        ];

        foreach ($accounts as $email) {
            $response = $this->post(route('login'), [
                'login' => $email,
                'password' => 'password',
            ]);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('dashboard'));
            $this->assertAuthenticated();

            // Coordinator can view dashboard and visits
            $this->get(route('dashboard'))->assertStatus(200);
            $this->get(route('visits.index'))->assertStatus(200);

            // Log out for next iteration
            $this->post(route('logout'));
            $this->assertGuest();
        }
    }

    public function test_buttons_are_visible_or_hidden_based_on_role_permissions(): void
    {
        $superadmin = User::where('email', 'superadmin@plant.test')->first() ?? $this->getAdmin();
        $admin = User::where('email', 'admin@plant.test')->first();
        $coordinator = User::where('email', 'coordinator@plant.test')->first();

        // 1. COORDINATOR: View only on plants, no export on reports, no register on visits
        if ($coordinator) {
            // Plants: View-only, no create/edit/delete buttons
            $response = $this->actingAs($coordinator)->get(route('plants.index'));
            $response->assertStatus(200);
            $response->assertDontSee('Register New Plant');
            $response->assertDontSee(route('plants.create'));
            $response->assertSee('View Only');

            // Reports: View-only, no export dropdown or CSV export buttons
            $reportResponse = $this->actingAs($coordinator)->get(route('reports.index'));
            $reportResponse->assertStatus(200);
            $reportResponse->assertDontSee('topbarExportDropdown');
            $reportResponse->assertDontSee(route('reports.export'));

            // Visits: No "Register Visitor" or "Sync 3rd Party" buttons
            $visitResponse = $this->actingAs($coordinator)->get(route('visits.index'));
            $visitResponse->assertStatus(200);
            $visitResponse->assertDontSee('Register Visitor');
            $visitResponse->assertDontSee('Sync 3rd Party');
            $visitResponse->assertDontSee(route('visits.create'));
        }

        // 2. ADMIN: Read-only on roles & permissions, create/edit on plants, can export reports
        if ($admin) {
            // Roles: View-only, no Add Role or edit/delete actions
            $roleResponse = $this->actingAs($admin)->get(route('roles.index'));
            $roleResponse->assertStatus(200);
            $roleResponse->assertDontSee('Add Role');
            $roleResponse->assertDontSee(route('roles.create'));
            $roleResponse->assertSee('View Only');

            // Permissions: View-only, no Add Permission
            $permResponse = $this->actingAs($admin)->get(route('permissions.index'));
            $permResponse->assertStatus(200);
            $permResponse->assertDontSee('Add Permission');
            $permResponse->assertDontSee(route('permissions.create'));
            $permResponse->assertSee('View Only');

            // Plants: Can create and edit, but NO delete
            $plantResponse = $this->actingAs($admin)->get(route('plants.index'));
            $plantResponse->assertStatus(200);
            $plantResponse->assertSee('Register New Plant');
            $plantResponse->assertSee(route('plants.create'));

            // Reports: Can export
            $reportResponse = $this->actingAs($admin)->get(route('reports.index'));
            $reportResponse->assertStatus(200);
            $reportResponse->assertSee('topbarExportDropdown');
            $reportResponse->assertSee(route('reports.export'));
        }

        // 3. SUPERADMIN: Full access to Add, Edit, Delete, and Export
        if ($superadmin) {
            $superPlants = $this->actingAs($superadmin)->get(route('plants.index'));
            $superPlants->assertStatus(200);
            $superPlants->assertSee('Register New Plant');
            $superPlants->assertSee(route('plants.create'));

            $superRoles = $this->actingAs($superadmin)->get(route('roles.index'));
            $superRoles->assertStatus(200);
            $superRoles->assertSee('Add Role');
            $superRoles->assertSee(route('roles.create'));

            $superReports = $this->actingAs($superadmin)->get(route('reports.index'));
            $superReports->assertStatus(200);
            $superReports->assertSee('topbarExportDropdown');
        }
    }
}

