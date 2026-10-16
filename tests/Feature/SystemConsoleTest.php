<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SystemConsoleTest extends TestCase
{
    protected function getSuperadmin(): User
    {
        return User::where('role', 'superadmin')->first()
            ?? User::factory()->create(['role' => 'superadmin', 'is_active' => true]);
    }

    protected function getAdmin(): User
    {
        return User::where('role', 'admin')->first()
            ?? User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    protected function getStaff(): User
    {
        return User::where('role', 'staff')->first()
            ?? User::factory()->create(['role' => 'staff', 'is_active' => true]);
    }

    public function test_superadmin_and_admin_can_access_system_console(): void
    {
        $superadmin = $this->getSuperadmin();
        $response = $this->actingAs($superadmin)->get(route('system_console.index'));
        $response->assertStatus(200);
        $response->assertSee('System Maintenance &amp; Artisan Console', false);
        $response->assertSee('Clear All Caches');
        $response->assertSee('Optimize &amp; Cache Application', false);
        $response->assertSee('Run Database Migrations');
        $response->assertSee("Seed Today's Plant Visits", false);

        $admin = $this->getAdmin();
        $response2 = $this->actingAs($admin)->get(route('system_console.index'));
        $response2->assertStatus(200);
    }

    public function test_guest_can_access_system_console_without_auth(): void
    {
        $response = $this->get(route('system_console.index'));
        $response->assertStatus(200);
        $response->assertSee('System Maintenance &amp; Artisan Console', false);
        $response->assertSee('Clear All Caches');
        $response->assertSee('Admin Login');
    }

    public function test_guest_can_execute_whitelisted_command_without_auth(): void
    {
        $response = $this->postJson(route('system_console.execute'), [
            'command' => 'optimize:clear',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'command',
            'output',
            'exit_code',
            'duration_ms',
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_admin_can_execute_whitelisted_command_via_ajax(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->postJson(route('system_console.execute'), [
            'command' => 'optimize:clear',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'command',
            'output',
            'exit_code',
            'duration_ms',
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_unauthorized_command_is_rejected(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->postJson(route('system_console.execute'), [
            'command' => 'key:generate', // not in whitelist
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_direct_clear_cache_route_works(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('system_console.direct_clear_cache'));
        $response->assertRedirect(route('system_console.index'));
        $response->assertSessionHas('success');
    }

    public function test_logs_endpoint_returns_json(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->getJson(route('system_console.logs'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'size',
            'content',
        ]);
    }
}
