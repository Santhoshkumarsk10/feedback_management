<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Shift;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffShiftOperationsTest extends TestCase
{
    private function getStaff(): User
    {
        $staff = User::where('role', 'staff')->first();
        if (!$staff) {
            $staff = User::factory()->create([
                'name' => 'Shift Staff Engineer',
                'email' => 'staff_duty@plant.test',
                'role' => 'staff',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $staff->syncRoles(['staff']);
        }
        return $staff;
    }

    public function test_shift_model_tracks_current_duty_shift_and_progress(): void
    {
        $shift = Shift::current();
        $this->assertNotNull($shift);
        $this->assertNotEmpty($shift->name);

        $progress = $shift->shift_progress;
        $this->assertArrayHasKey('percent', $progress);
        $this->assertArrayHasKey('remaining_formatted', $progress);
    }

    public function test_staff_dashboard_displays_live_duty_shift_and_today_visitors_queue(): void
    {
        $staff = $this->getStaff();

        $response = $this->actingAs($staff)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('LIVE DUTY SHIFT TRACKER');
        $response->assertSee('Today\'s Plant Visitors', false);
        $response->assertSee('Awaiting Feedback (Pending பாக்கி)', false);
        $response->assertSee('Feedback Completed');
    }

    public function test_staff_can_view_today_pending_visitors_and_see_submit_on_behalf_button(): void
    {
        $staff = $this->getStaff();
        $shift = Shift::current() ?? Shift::first();

        // Create a pending visit for today
        $visit = Visit::create([
            'organizer_id' => $staff->id,
            'shift_id' => $shift->id,
            'visitor_name' => 'Testing Pending Visitor',
            'visitor_company' => 'Test Company Pvt Ltd',
            'visitor_mobile' => '9876543210',
            'visit_date' => today(),
            'purpose' => 'Machine Inspection',
        ]);

        $response = $this->actingAs($staff)->get(route('dashboard', ['today_tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('Testing Pending Visitor');
        $response->assertSee('Submit on Behalf (அவருக்காக பதிவு செய்க)');
    }

    public function test_staff_can_access_submit_feedback_on_behalf_page(): void
    {
        $staff = $this->getStaff();
        $shift = Shift::current() ?? Shift::first();

        $visit = Visit::create([
            'organizer_id' => $staff->id,
            'shift_id' => $shift->id,
            'visitor_name' => 'Evaluation Candidate',
            'visitor_company' => 'Candidate Tooling Ltd',
            'visitor_mobile' => '9840998877',
            'visit_date' => today(),
            'purpose' => 'Die Casting Demo',
        ]);

        $response = $this->actingAs($staff)->get(route('visits.feedback.create', $visit));

        $response->assertStatus(200);
        $response->assertSee('Submit Feedback on Behalf');
        $response->assertSee('Evaluation Candidate');
        $response->assertSee('Overall Tour Satisfaction Rating');
    }

    public function test_staff_can_submit_feedback_on_behalf_of_visitor(): void
    {
        $staff = $this->getStaff();
        $shift = Shift::current() ?? Shift::first();
        $questions = Question::active()->get();

        $visit = Visit::create([
            'organizer_id' => $staff->id,
            'shift_id' => $shift->id,
            'visitor_name' => 'Ramanathan V',
            'visitor_company' => 'Ramanathan Auto Components',
            'visitor_mobile' => '9840112299',
            'visit_date' => today(),
            'purpose' => 'High Speed Press Trial',
        ]);

        $answersData = [];
        foreach ($questions as $index => $q) {
            $answersData[$index] = [
                'question_id' => $q->id,
                'answer' => 5,
            ];
        }

        $response = $this->actingAs($staff)->post(route('visits.feedback.store', $visit), [
            'overall_rating' => 5,
            'comments' => 'Demonstration went smoothly. Excellent precision achieved during trial.',
            'answers' => $answersData,
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Confirm feedback now exists
        $this->assertDatabaseHas('feedbacks', [
            'visit_id' => $visit->id,
            'overall_rating' => 5,
        ]);

        $this->assertTrue($visit->fresh()->feedback()->exists());
    }

    public function test_staff_can_register_new_visitor_for_active_shift(): void
    {
        $staff = $this->getStaff();
        $shift = Shift::current() ?? Shift::first();

        // Access create form
        $this->actingAs($staff)->get(route('visits.create'))->assertStatus(200);

        // Store new visitor
        $response = $this->actingAs($staff)->post(route('visits.store'), [
            'visitor_name' => 'Senthil Nathan',
            'visitor_company' => 'Apex Automation',
            'visitor_mobile' => '9790112233',
            'visitor_designation' => 'Technical Manager',
            'purpose' => 'Assembly Automation',
            'shift_id' => $shift->id,
            'visit_date' => today()->toDateString(),
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('visits', [
            'visitor_name' => 'Senthil Nathan',
            'visitor_company' => 'Apex Automation',
            'shift_id' => $shift->id,
        ]);
    }
}
