<?php

namespace Tests\Feature;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SupervisorShiftReportTest extends TestCase
{
    private function getSupervisor(): User
    {
        $supervisor = User::where('role', 'supervisor')->first();
        if (!$supervisor) {
            $supervisor = User::factory()->create([
                'name' => 'Supervisor Test',
                'email' => 'supervisor_test@plant.test',
                'role' => 'supervisor',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $supervisor->syncRoles(['supervisor']);
        }
        return $supervisor;
    }

    public function test_supervisor_can_view_reports_with_shift_overview(): void
    {
        $supervisor = $this->getSupervisor();

        $response = $this->actingAs($supervisor)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Shift-Wise Performance Overview');
        $response->assertSee('Shift A');
        $response->assertSee('Shift B');
        $response->assertSee('Shift C');
    }

    public function test_supervisor_can_filter_reports_by_shift(): void
    {
        $supervisor = $this->getSupervisor();
        $shiftA = Shift::where('code', 'SHIFT-A')->firstOrFail();

        $response = $this->actingAs($supervisor)->get(route('reports.index', ['shift_id' => $shiftA->id]));

        $response->assertStatus(200);
        $response->assertSee('Shift Filter Applied');
        $response->assertSee('Shift A');
        $response->assertSee('Clear Shift Filter');
    }

    public function test_supervisor_can_export_csv_with_shift_filter(): void
    {
        $supervisor = $this->getSupervisor();
        $shiftA = Shift::where('code', 'SHIFT-A')->firstOrFail();

        $response = $this->actingAs($supervisor)->get(route('reports.export', ['shift_id' => $shiftA->id]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        
        // Grab stream output
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        $this->assertStringContainsString('Shift Filter', $csvContent);
        $this->assertStringContainsString('Shift A', $csvContent);
    }

    public function test_supervisor_can_filter_visits_by_shift(): void
    {
        $supervisor = $this->getSupervisor();
        $shiftA = Shift::where('code', 'SHIFT-A')->firstOrFail();

        $response = $this->actingAs($supervisor)->get(route('visits.index', ['shift_id' => $shiftA->id]));

        $response->assertStatus(200);
        $response->assertSee('Shift A');
    }

    public function test_supervisor_can_filter_feedbacks_by_shift(): void
    {
        $supervisor = $this->getSupervisor();
        $shiftB = Shift::where('code', 'SHIFT-B')->firstOrFail();

        $response = $this->actingAs($supervisor)->get(route('feedbacks.index', ['shift_id' => $shiftB->id]));

        $response->assertStatus(200);
        $response->assertSee('Shift B');
    }
}
