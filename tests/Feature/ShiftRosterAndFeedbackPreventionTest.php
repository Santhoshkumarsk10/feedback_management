<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Shift;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ShiftRosterAndFeedbackPreventionTest extends TestCase
{
    use DatabaseTransactions;

    private function createShift(string $name, string $code, string $start, string $end): Shift
    {
        return Shift::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'start_time' => $start,
                'end_time' => $end,
                'is_active' => true,
            ]
        );
    }

    private function createQuestions(): array
    {
        $questions = Question::active()->get();
        if ($questions->isEmpty()) {
            $q1 = Question::create([
                'question' => 'How would you rate the experience?',
                'type' => 'rating',
                'section' => 'Feedback Questions',
                'is_required' => true,
                'is_active' => true,
                'order' => 1,
            ]);
            $questions = collect([$q1]);
        }

        $answers = [];
        foreach ($questions as $q) {
            $ans = ($q->type === 'rating' || $q->type === 'rating_5') ? '5' : ($q->options ? $q->options[0] : 'Satisfactory');
            $answers[] = [
                'question_id' => $q->id,
                'answer' => $ans,
            ];
        }

        return $answers;
    }

    public function test_duplicate_feedback_prevention_blocks_second_submission(): void
    {
        $staff = User::where('role', 'staff')->first() ?? User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $answers = $this->createQuestions();

        $payload = [
            'organizer_id' => $staff->id,
            'visitor_name' => 'Arun Kumar',
            'visitor_company' => 'Auto Tech Ltd',
            'visitor_mobile' => '9840123456',
            'purpose' => 'Machine Demo',
            'overall_rating' => 5,
            'answers' => $answers,
        ];

        // 1. First submission succeeds
        $res1 = $this->postJson('/api/visitor/feedback', $payload);
        $res1->assertStatus(201);
        $feedbackId = $res1->json('feedback_id');
        $this->assertNotNull($feedbackId);

        // 2. Duplicate submission for the same visitor today is blocked per Section 6.2 requirement 4
        $res2 = $this->postJson('/api/visitor/feedback', $payload);
        $res2->assertStatus(422);
        $res2->assertSee('Thank you! Your feedback has already been received for this visit.');
    }

    public function test_off_duty_staff_is_blocked_with_not_in_current_shift_message(): void
    {
        // Define Shift A (morning) and Shift B (evening)
        $shiftA = $this->createShift('Shift A', 'SHIFT-A', '06:00:00', '14:00:00');
        $shiftB = $this->createShift('Shift B', 'SHIFT-B', '14:00:00', '22:00:00');

        $staffEmail = 'morning_staff_' . uniqid() . '@plant.test';
        $staff = User::factory()->create([
            'role' => 'staff',
            'email' => $staffEmail,
            'password' => bcrypt('secret123'),
            'shift_id' => $shiftA->id, // Staff rostered only for Shift A
            'is_active' => true,
        ]);

        // Travel to 16:30 (inside Shift B, outside Shift A)
        Carbon::setTestNow(Carbon::parse('2026-10-08 16:30:00', config('app.plant_timezone', 'Asia/Kolkata')));

        $this->assertFalse($staff->isOnDuty());

        // Attempt login via API
        $res = $this->postJson('/api/login', [
            'login' => $staffEmail,
            'password' => 'secret123',
        ]);

        $res->assertStatus(422);
        $res->assertSee('Not in current shift');

        Carbon::setTestNow(); // reset
    }

    public function test_admin_is_exempt_and_can_login_anytime(): void
    {
        $shiftA = $this->createShift('Shift A', 'SHIFT-A', '06:00:00', '14:00:00');
        $adminEmail = 'admin_exempt_' . uniqid() . '@plant.test';
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => $adminEmail,
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        // Travel to 23:00 (off-shift)
        Carbon::setTestNow(Carbon::parse('2026-10-08 23:00:00', config('app.plant_timezone', 'Asia/Kolkata')));

        $this->assertTrue($admin->isOnDuty());

        $res = $this->postJson('/api/login', [
            'login' => $adminEmail,
            'password' => 'secret123',
        ]);

        $res->assertStatus(200);
        $res->assertJsonStructure(['access_token', 'user']);

        Carbon::setTestNow();
    }

    public function test_submitting_on_behalf_keeps_original_host_and_records_submitter(): void
    {
        $hostStaff = User::factory()->create(['role' => 'staff', 'name' => 'Host Engineer', 'is_active' => true]);
        $submittingStaff = User::factory()->create(['role' => 'staff', 'name' => 'Covering Staff', 'is_active' => true]);

        $shift = $this->createShift('Shift A', 'SHIFT-A', '06:00:00', '23:00:00');

        $visit = Visit::create([
            'organizer_id' => $hostStaff->id,
            'shift_id' => $shift->id,
            'visitor_name' => 'Kavitha Raman',
            'visitor_company' => 'Precision Plastics',
            'visit_date' => today(),
        ]);

        $answers = $this->createQuestions();

        $token = auth('api')->login($submittingStaff);

        $res = $this->withToken($token)->postJson("/api/organizer/visits/{$visit->id}/feedback", [
            'overall_rating' => 4,
            'comments' => 'Demonstration completed smoothly by covering staff.',
            'answers' => $answers,
        ]);

        $res->assertStatus(201);

        $feedback = Feedback::where('visit_id', $visit->id)->first();
        $this->assertNotNull($feedback);
        // Original host preserved
        $this->assertEquals($hostStaff->id, $feedback->organizer_id);
        // Submitter recorded
        $this->assertEquals($submittingStaff->id, $feedback->submitted_by_staff_id);
        $this->assertEquals('staff_assisted', $feedback->submitted_mode);
        $this->assertTrue($feedback->is_staff_assisted);
    }
}
