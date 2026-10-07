<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Shift;
use App\Models\User;
use Tests\TestCase;

class ShiftCrudTest extends TestCase
{
    private function getAdminUser(): User
    {
        return User::where('role', 'superadmin')->first() 
            ?? User::where('role', 'admin')->first()
            ?? User::factory()->create(['role' => 'superadmin']);
    }

    public function test_shift_index_page_displays_all_shifts(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('shifts.index'));

        $response->assertStatus(200);
        $response->assertSee('Shift A');
        $response->assertSee('Shift B');
        $response->assertSee('Shift C');
        $response->assertSee('06:00 – 14:00');
        $response->assertSee('14:00 – 22:00');
        $response->assertSee('22:00 – 06:00');
    }

    public function test_shift_create_page_renders_successfully(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('shifts.create'));

        $response->assertStatus(200);
        $response->assertSee('Register Factory Shift');
        $response->assertSee('Quick Preset Fill');
    }

    public function test_store_creates_new_shift_and_audit_log(): void
    {
        $admin = $this->getAdminUser();

        // Delete test shift if exists
        Shift::where('code', 'SHIFT-TEST')->delete();

        $response = $this->actingAs($admin)->post(route('shifts.store'), [
            'code' => 'SHIFT-TEST',
            'name' => 'Test General Shift',
            'start_time' => '08:30',
            'end_time' => '17:30',
            'description' => 'Test maintenance & day shift',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('shifts.index'));
        $this->assertDatabaseHas('shifts', [
            'code' => 'SHIFT-TEST',
            'name' => 'Test General Shift',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'shifts',
            'action' => 'create',
        ]);
    }

    public function test_shift_edit_page_renders(): void
    {
        $admin = $this->getAdminUser();
        $shift = Shift::where('code', 'SHIFT-A')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('shifts.edit', $shift));

        $response->assertStatus(200);
        $response->assertSee('Shift A');
        $response->assertSee('SHIFT-A');
    }

    public function test_update_modifies_existing_shift(): void
    {
        $admin = $this->getAdminUser();
        $shift = Shift::where('code', 'SHIFT-TEST')->first() ?? Shift::create([
            'code' => 'SHIFT-TEST',
            'name' => 'Test Shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('shifts.update', $shift), [
            'code' => 'SHIFT-TEST',
            'name' => 'Updated Shift Name',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'description' => 'Updated description notes',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('shifts.index'));
        $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'name' => 'Updated Shift Name',
        ]);
    }

    public function test_toggle_inverts_shift_status(): void
    {
        $admin = $this->getAdminUser();
        $shift = Shift::where('code', 'SHIFT-TEST')->first();

        $initialStatus = $shift->is_active;

        $response = $this->actingAs($admin)->patch(route('shifts.toggle', $shift));

        $response->assertStatus(302);
        $this->assertEquals(! $initialStatus, $shift->fresh()->is_active);
    }

    public function test_destroy_deletes_shift(): void
    {
        $admin = $this->getAdminUser();
        $shift = Shift::where('code', 'SHIFT-TEST')->first();

        $response = $this->actingAs($admin)->delete(route('shifts.destroy', $shift));

        $response->assertRedirect(route('shifts.index'));
        $this->assertDatabaseMissing('shifts', [
            'id' => $shift->id,
        ]);
    }
}
