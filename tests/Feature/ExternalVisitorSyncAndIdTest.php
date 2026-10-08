<?php

namespace Tests\Feature;

use App\Models\Shift;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Services\ExternalVisitorSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExternalVisitorSyncAndIdTest extends TestCase
{
    public function test_visitor_model_generates_unique_id()
    {
        $id1 = Visitor::generateUniqueVisitorId();
        $this->assertStringStartsWith('VIS-', $id1);

        $v1 = Visitor::create([
            'visitor_id' => $id1,
            'name' => 'Karthikeyan',
            'mobile' => '9876500001',
        ]);

        $id2 = Visitor::generateUniqueVisitorId();
        $this->assertNotEquals($id1, $id2);
        $this->assertDatabaseHas('visitors', ['visitor_id' => $id1]);
    }

    public function test_external_sync_service_upserts_unique_visitors_and_today_visits()
    {
        $shift = Shift::firstOrCreate(
            ['code' => 'SHIFT-B'],
            ['name' => 'Shift B', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'is_active' => true]
        );

        $service = app(ExternalVisitorSyncService::class);
        $result = $service->syncVisitors();

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['synced_visitors']);

        // Check visitor created in master table
        $visitor = Visitor::where('external_id', 'EXT-PASS-8812')->first();
        $this->assertNotNull($visitor);
        $this->assertEquals('Aravind Swaminathan', $visitor->name);
        $this->assertEquals('EXT-PASS-8812', $visitor->visitor_id);

        // Check visit created in visits table for today
        $visit = Visit::where('visitor_id', $visitor->id)->whereDate('visit_date', today())->first();
        $this->assertNotNull($visit);
        $this->assertEquals($visitor->visitor_id, $visit->visitor_code);
        $this->assertEquals(today()->toDateString(), $visit->visit_date->toDateString());

        // Second sync should not duplicate visitors
        $result2 = $service->syncVisitors();
        $this->assertEquals(0, $result2['created_visits']);
        $this->assertEquals(1, Visitor::where('external_id', 'EXT-PASS-8812')->count());
    }

    public function test_visitor_lookup_by_unique_visitor_id()
    {
        $uniqueId = Visitor::generateUniqueVisitorId();
        $visitor = Visitor::create([
            'visitor_id' => $uniqueId,
            'name' => 'Deepak Chopra',
            'company' => 'Tata Electronics',
            'mobile' => '984011' . rand(1000, 9999),
        ]);

        $response = $this->getJson("/api/visitor/lookup/{$visitor->visitor_id}");

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('visitor.name', 'Deepak Chopra');
        $response->assertJsonPath('visitor.visitor_id', $uniqueId);
        $response->assertJsonPath('visit.has_feedback', false);
    }

    public function test_staff_can_trigger_visitor_sync_via_web_route()
    {
        $staff = User::where('role', 'staff')->first() ?: User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->post(route('visits.sync'));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_organizer_can_trigger_visitor_sync_via_api()
    {
        $staff = User::where('role', 'staff')->first() ?: User::factory()->create(['role' => 'staff']);
        $token = auth('api')->login($staff);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/organizer/visitors/sync');

        $response->assertOk();
        $response->assertJsonPath('success', true);
    }
}
