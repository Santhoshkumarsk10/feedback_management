<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportExportFormatsTest extends TestCase
{
    private function getSupervisor(): User
    {
        $supervisor = User::where('role', 'supervisor')->first();
        if (!$supervisor) {
            $supervisor = User::factory()->create([
                'name' => 'Supervisor Export Test',
                'email' => 'supervisor_export@plant.test',
                'role' => 'supervisor',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $supervisor->syncRoles(['supervisor']);
        }
        return $supervisor;
    }

    public function test_supervisor_can_export_staff_performance_as_excel(): void
    {
        $supervisor = $this->getSupervisor();

        $response = $this->actingAs($supervisor)->get(route('reports.export', [
            'type' => 'staff',
            'format' => 'excel',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_supervisor_can_export_staff_performance_as_pdf(): void
    {
        $supervisor = $this->getSupervisor();

        $response = $this->actingAs($supervisor)->get(route('reports.export', [
            'type' => 'staff',
            'format' => 'pdf',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition'));
    }

    public function test_supervisor_can_export_low_rating_report_as_excel_and_pdf(): void
    {
        $supervisor = $this->getSupervisor();

        // Test Excel
        $excelResponse = $this->actingAs($supervisor)->get(route('reports.export', [
            'type' => 'low_rating',
            'format' => 'excel',
        ]));
        $excelResponse->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $excelResponse->headers->get('content-type'));

        // Test PDF
        $pdfResponse = $this->actingAs($supervisor)->get(route('reports.export', [
            'type' => 'low_rating',
            'format' => 'pdf',
        ]));
        $pdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_supervisor_can_export_pending_queue_report_as_excel_and_pdf(): void
    {
        $supervisor = $this->getSupervisor();

        // Test Excel
        $excelResponse = $this->actingAs($supervisor)->get(route('reports.export', [
            'type' => 'pending',
            'format' => 'excel',
        ]));
        $excelResponse->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $excelResponse->headers->get('content-type'));

        // Test PDF
        $pdfResponse = $this->actingAs($supervisor)->get(route('reports.export', [
            'type' => 'pending',
            'format' => 'pdf',
        ]));
        $pdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_reports_index_page_displays_excel_and_pdf_export_actions(): void
    {
        $supervisor = $this->getSupervisor();

        $response = $this->actingAs($supervisor)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('format=excel');
        $response->assertSee('format=pdf');
        $response->assertSee('Excel (.xlsx)');
        $response->assertSee('PDF Document (.pdf)');
    }
}
