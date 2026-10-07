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
            'permissions' => ['view-visits', 'submit-feedback', 'view-feedbacks'],
        ]);

        $response->assertRedirect(route('roles.index'));

        $role = Role::where('name', 'test_custom_role')->first();
        $this->assertNotNull($role);
        $this->assertEquals('Custom Shift Leader', $role->display_name);
        $this->assertTrue($role->hasPermissionTo('view-visits'));
        $this->assertTrue($role->hasPermissionTo('submit-feedback'));
        $this->assertFalse($role->hasPermissionTo('manage-users'));
    }

    public function test_edit_role_page_renders_with_permission_matrix(): void
    {
        $admin = $this->getAdmin();
        $role = Role::where('name', 'test_custom_role')->first() ?? Role::where('name', 'staff')->first();

        $response = $this->actingAs($admin)->get(route('roles.edit', $role));
        $response->assertStatus(200);
        $response->assertSee('Capability Permissions Matrix');
        $response->assertSee('view-visits');
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
            'permissions' => ['view-visits', 'manage-shifts'],
        ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertTrue($role->fresh()->hasPermissionTo('manage-shifts'));
        $this->assertFalse($role->fresh()->hasPermissionTo('submit-feedback'));
    }

    public function test_permissions_index_page_displays_all_capabilities(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('permissions.index'));
        $response->assertStatus(200);
        $response->assertSee('Capability Permissions Directory');
        $response->assertSee('access-web-panel');
        $response->assertSee('manage-shifts');
    }

    public function test_create_and_store_new_custom_permission(): void
    {
        $admin = $this->getAdmin();

        // Clean up if exists
        Permission::where('name', 'custom-audit-export')->delete();

        $staffRole = Role::where('name', 'staff')->first();

        $response = $this->actingAs($admin)->post(route('permissions.store'), [
            'name' => 'custom-audit-export',
            'display_name' => 'Export Shift Audits',
            'module' => 'Security & Audits',
            'description' => 'Allows exporting historical shift logs and evaluation trails',
            'roles' => [$staffRole->id],
        ]);

        $response->assertRedirect(route('permissions.index'));

        $perm = Permission::where('name', 'custom-audit-export')->first();
        $this->assertNotNull($perm);
        $this->assertEquals('Export Shift Audits', $perm->display_name);
        $this->assertEquals('Security & Audits', $perm->module);
        $this->assertTrue($staffRole->fresh()->hasPermissionTo('custom-audit-export'));
    }

    public function test_core_permissions_protected_from_deletion(): void
    {
        $admin = $this->getAdmin();
        $corePerm = Permission::where('name', 'access-web-panel')->firstOrFail();

        $response = $this->actingAs($admin)->delete(route('permissions.destroy', $corePerm));
        $response->assertSessionHas('error');

        $this->assertNotNull(Permission::where('name', 'access-web-panel')->first());
    }

    public function test_custom_permission_can_be_deleted(): void
    {
        $admin = $this->getAdmin();
        $perm = Permission::where('name', 'custom-audit-export')->first();
        if ($perm) {
            $response = $this->actingAs($admin)->delete(route('permissions.destroy', $perm));
            $response->assertRedirect(route('permissions.index'));
            $this->assertNull(Permission::where('name', 'custom-audit-export')->first());
        }
    }

    public function test_supervisor_and_staff_forbidden_from_permission_master(): void
    {
        $supervisor = User::where('role', 'supervisor')->first();
        $staff = User::where('role', 'staff')->first();

        $this->actingAs($supervisor)->get(route('permissions.index'))->assertStatus(403);
        $this->actingAs($staff)->get(route('permissions.index'))->assertStatus(403);
    }
}
