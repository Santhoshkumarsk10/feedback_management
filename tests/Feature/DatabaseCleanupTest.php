<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\DatabaseMaintenanceController;
use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\Role;
use App\Models\Shift;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseCleanupTest extends TestCase
{
    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@plant.test'],
            [
                'name' => 'Admin Tester',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->staff = User::firstOrCreate(
            ['email' => 'staff_test@plant.test'],
            [
                'name' => 'Staff Tester',
                'password' => bcrypt('password'),
                'role' => 'staff',
                'is_active' => true,
            ]
        );
    }

    public function test_admin_can_access_database_cleanup_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('database_cleanup.index'));

        $response->assertStatus(200);
        $response->assertSee('Database Table Cleanup');
        $response->assertSee('Strictly Protected Tables');
        $response->assertSee('Unshielded Tables (Select to Clear)');
        $response->assertSee('users');
        $response->assertSee('audit_logs');
        $response->assertSee('password_reset_tokens');
        $response->assertSee('role_has_permissions');
    }

    public function test_staff_cannot_access_database_cleanup(): void
    {
        $response = $this->actingAs($this->staff)->get(route('database_cleanup.index'));
        $response->assertStatus(403);
    }

    public function test_clearing_protected_table_is_strictly_blocked(): void
    {
        foreach (['users', 'audit_logs', 'password_reset_tokens', 'role_has_permissions', 'permissions', 'sessions'] as $protectedTable) {
            $response = $this->actingAs($this->admin)->post(route('database_cleanup.clear'), [
                'tables' => [$protectedTable],
            ]);

            $response->assertSessionHas('error');
            // Ensure table is still populated
            if ($protectedTable === 'users') {
                $this->assertDatabaseHas('users', ['email' => $this->admin->email]);
            }
        }
    }

    public function test_admin_can_clear_unshielded_tables(): void
    {
        \Illuminate\Support\Facades\DB::table('job_batches')->insert([
            'id' => 'test_batch_123',
            'name' => 'Test Batch',
            'total_jobs' => 1,
            'pending_jobs' => 1,
            'failed_jobs' => 0,
            'failed_job_ids' => '[]',
            'created_at' => now()->timestamp,
        ]);

        $this->assertDatabaseHas('job_batches', ['id' => 'test_batch_123']);

        $response = $this->actingAs($this->admin)->post(route('database_cleanup.clear'), [
            'tables' => ['job_batches'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Confirm job_batches table was cleared
        $this->assertEquals(0, \Illuminate\Support\Facades\DB::table('job_batches')->count());

        // Confirm users table was NOT cleared
        $this->assertDatabaseHas('users', ['email' => $this->admin->email]);
    }
}
