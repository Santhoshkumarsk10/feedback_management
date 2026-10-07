<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAndAuthenticationTest extends TestCase
{
    private function getUser(string $role): User
    {
        $user = User::where('role', $role)->first();
        if (!$user) {
            $user = User::factory()->create([
                'role' => $role,
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $user->syncRoles([$role === 'organizer' ? 'staff' : $role]);
        }
        return $user;
    }

    public function test_superadmin_can_login_to_web_and_access_all_modules(): void
    {
        $super = $this->getUser('superadmin');

        $loginResponse = $this->post(route('login'), [
            'login' => $super->email,
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));

        $this->actingAs($super)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($super)->get(route('plants.index'))->assertStatus(200);
        $this->actingAs($super)->get(route('roles.index'))->assertStatus(200);
        $this->actingAs($super)->get(route('shifts.index'))->assertStatus(200);
        $this->actingAs($super)->get(route('users.index'))->assertStatus(200);
        $this->actingAs($super)->get(route('reports.index'))->assertStatus(200);
        $this->actingAs($super)->get(route('visits.index'))->assertStatus(200);
        $this->actingAs($super)->get(route('feedbacks.index'))->assertStatus(200);
    }

    public function test_admin_can_login_to_web_and_access_admin_modules(): void
    {
        $admin = $this->getUser('admin');

        $loginResponse = $this->post(route('login'), [
            'login' => $admin->email,
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));

        $this->actingAs($admin)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('shifts.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('plants.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('reports.index'))->assertStatus(200);
    }

    public function test_supervisor_can_login_to_web_view_reports_and_blocked_from_masters(): void
    {
        $supervisor = $this->getUser('supervisor');

        // Web login succeeds
        $loginResponse = $this->post(route('login'), [
            'login' => $supervisor->email,
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));

        // Can access dashboard, reports, visits, feedbacks
        $this->actingAs($supervisor)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('reports.index'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('visits.index'))->assertStatus(200);
        $this->actingAs($supervisor)->get(route('feedbacks.index'))->assertStatus(200);

        // FORBIDDEN (403) from System Masters
        $this->actingAs($supervisor)->get(route('plants.index'))->assertStatus(403);
        $this->actingAs($supervisor)->get(route('roles.index'))->assertStatus(403);
        $this->actingAs($supervisor)->get(route('shifts.index'))->assertStatus(403);
        $this->actingAs($supervisor)->get(route('users.index'))->assertStatus(403);
    }

    public function test_staff_can_login_to_web_and_is_blocked_from_masters_and_reports(): void
    {
        $staff = $this->getUser('staff');

        // Web login succeeds
        $loginResponse = $this->post(route('login'), [
            'login' => $staff->email,
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));

        // Can access dashboard, visits, feedbacks
        $this->actingAs($staff)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($staff)->get(route('visits.index'))->assertStatus(200);
        $this->actingAs($staff)->get(route('feedbacks.index'))->assertStatus(200);

        // FORBIDDEN (403) from reports and system masters
        $this->actingAs($staff)->get(route('reports.index'))->assertStatus(403);
        $this->actingAs($staff)->get(route('plants.index'))->assertStatus(403);
        $this->actingAs($staff)->get(route('shifts.index'))->assertStatus(403);
        $this->actingAs($staff)->get(route('users.index'))->assertStatus(403);
    }

    public function test_mobile_login_permissions(): void
    {
        $staff = $this->getUser('staff');
        $supervisor = $this->getUser('supervisor');

        // Staff can log into mobile
        $staffResponse = $this->postJson(route('mobile.organizer.login'), [
            'login' => $staff->email,
            'password' => 'password',
        ]);
        $staffResponse->assertStatus(200)->assertJson(['success' => true]);

        // Supervisor cannot log into mobile organizer
        $supervisorResponse = $this->postJson(route('mobile.organizer.login'), [
            'login' => $supervisor->email,
            'password' => 'password',
        ]);
        $supervisorResponse->assertStatus(403);
    }
}
