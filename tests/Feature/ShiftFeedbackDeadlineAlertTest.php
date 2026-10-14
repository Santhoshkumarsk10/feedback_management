<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Shift;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ShiftFeedbackDeadlineAlertTest extends TestCase
{
    private function getStaff(): User
    {
        $staff = User::where('role', 'staff')->first();
        if (!$staff) {
            $staff = User::factory()->create([
                'name' => 'Shift A Staff Engineer',
                'email' => 'staff_a@plant.test',
                'role' => 'staff',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $staff->syncRoles(['staff']);
        }
        return $staff;
    }

    public function test_shift_a_alerts_staff_when_closing_within_30_minutes_with_pending_feedback(): void
    {
        $shiftA = Shift::where('code', 'SHIFT-A')->first();
        if (!$shiftA) {
            $shiftA = Shift::create([
                'name' => 'Shift A',
                'code' => 'SHIFT-A',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'is_active' => true,
            ]);
        }

        $staff = $this->getStaff();

        // Create visit for Shift A today without feedback
        $visit = Visit::create([
            'organizer_id' => $staff->id,
            'shift_id' => $shiftA->id,
            'visitor_name' => 'John Doe - Demo Visitor',
            'visitor_company' => 'Customer Corp',
            'visitor_mobile' => '9876543211',
            'visit_date' => today(),
            'purpose' => 'Machine Demo',
        ]);

        // Freeze time at 13:40 (20 minutes before 14:00 Shift A close)
        Carbon::setTestNow(Carbon::parse('today 13:40:00', config('app.plant_timezone', 'Asia/Kolkata')));

        $this->assertTrue($shiftA->is_currently_active);
        $this->assertTrue($shiftA->isClosingSoon(30));
        $this->assertEquals(20, $shiftA->shift_progress['remaining_minutes']);

        // Check alerts helper
        $alerts = Shift::getFeedbackCloseAlerts(30);
        $this->assertNotEmpty($alerts);
        $alertA = collect($alerts)->firstWhere('shift_code', 'SHIFT-A');
        $this->assertNotNull($alertA);
        $this->assertGreaterThanOrEqual(1, $alertA['pending_count']);
        $this->assertStringContainsString('Closing in 20 mins', $alertA['remaining_text']);

        // Check visit model accessor
        $this->assertTrue($visit->is_shift_closing_soon_alert);
        $this->assertEquals('Shift ends in 20m', $visit->shift_close_alert_text);

        // Check Staff viewing Shift Queue displays alert notification
        $response = $this->actingAs($staff)->get(route('shift_queue'));
        $response->assertStatus(200);
        $response->assertSee('Staff Alert: Shift A Feedback Due', false);
        $response->assertSee('Closing in 20 mins');

        // Check Staff viewing Dashboard displays alert notification
        $dashResponse = $this->actingAs($staff)->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Shift Feedback Alert: Shift A', false);

        // Check Live Feed API includes shift_alerts
        $apiResponse = $this->actingAs($staff)->get(route('dashboard.live_feed'));
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment(['shift_code' => 'SHIFT-A']);

        // Clean up Carbon mock
        Carbon::setTestNow();
    }

    public function test_alert_clears_once_visitor_submits_feedback(): void
    {
        $shiftA = Shift::where('code', 'SHIFT-A')->first();
        $staff = $this->getStaff();

        $visit = Visit::create([
            'organizer_id' => $staff->id,
            'shift_id' => $shiftA->id,
            'visitor_name' => 'Completed Visitor',
            'visitor_company' => 'Acme Tooling',
            'visitor_mobile' => '9876543212',
            'visit_date' => today(),
        ]);

        Carbon::setTestNow(Carbon::parse('today 13:45:00', config('app.plant_timezone', 'Asia/Kolkata')));

        // Initially alerts
        $this->assertTrue($visit->fresh()->is_shift_closing_soon_alert);

        // Submit feedback
        Feedback::create([
            'visit_id' => $visit->id,
            'organizer_id' => $staff->id,
            'overall_rating' => 5,
            'feedback_text' => 'Excellent plant tour',
            'submitted_at' => now(),
        ]);

        // Alert should now be cleared
        $this->assertFalse($visit->fresh()->is_shift_closing_soon_alert);

        Carbon::setTestNow();
    }

    public function test_does_not_alert_before_30_minutes_threshold(): void
    {
        $shiftA = Shift::where('code', 'SHIFT-A')->first();
        $staff = $this->getStaff();

        $visit = Visit::create([
            'organizer_id' => $staff->id,
            'shift_id' => $shiftA->id,
            'visitor_name' => 'Early Visitor',
            'visitor_company' => 'Early Corp',
            'visitor_mobile' => '9876543213',
            'visit_date' => today(),
        ]);

        // Freeze time at 11:00 (3 hours before shift close, > 30 minutes)
        Carbon::setTestNow(Carbon::parse('today 11:00:00', config('app.plant_timezone', 'Asia/Kolkata')));

        $this->assertTrue($shiftA->is_currently_active);
        $this->assertFalse($shiftA->isClosingSoon(30));

        $alerts = Shift::getFeedbackCloseAlerts(30);
        $alertA = collect($alerts)->firstWhere('shift_code', 'SHIFT-A');
        $this->assertNull($alertA);

        $this->assertFalse($visit->fresh()->is_shift_closing_soon_alert);

        Carbon::setTestNow();
    }
}
