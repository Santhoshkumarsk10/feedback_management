<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VisitorFeedbackExpirationTest extends TestCase
{
    use DatabaseTransactions;

    private User $staff;
    private Question $question;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->question = Question::create([
            'section' => 'Overall Experience',
            'question' => 'How satisfied are you with today\'s plant visit?',
            'type' => 'rating',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function getValidAnswers(string $rating = '5'): array
    {
        return Question::active()->where('is_required', true)->get()->map(fn ($q) => [
            'question_id' => $q->id,
            'answer' => $q->type === 'rating' ? $rating : 'Yes',
        ])->toArray();
    }

    public function test_visit_model_correctly_identifies_expired_feedback(): void
    {
        $todayVisit = Visit::create([
            'visitor_name' => 'Alice Today',
            'visitor_company' => 'Modern Tech',
            'visitor_mobile' => '9876543210',
            'organizer_id' => $this->staff->id,
            'visit_date' => today(),
        ]);

        $pastVisit = Visit::create([
            'visitor_name' => 'Bob Yesterday',
            'visitor_company' => 'Ancient Tools',
            'visitor_mobile' => '9876543211',
            'organizer_id' => $this->staff->id,
            'visit_date' => today()->subDay(),
        ]);

        $this->assertFalse($todayVisit->is_feedback_expired);
        $this->assertTrue($todayVisit->can_submit_feedback);

        $this->assertTrue($pastVisit->is_feedback_expired);
        $this->assertFalse($pastVisit->can_submit_feedback);
    }

    public function test_create_feedback_web_redirects_with_error_for_expired_visit(): void
    {
        $pastVisit = Visit::create([
            'visitor_name' => 'Charlie Past',
            'visitor_company' => 'Old Co',
            'visitor_mobile' => '9876543212',
            'organizer_id' => $this->staff->id,
            'visit_date' => today()->subDays(2),
        ]);

        $response = $this->actingAs($this->staff)->get(route('visits.feedback.create', $pastVisit));

        $response->assertRedirect(route('shift_queue'));
        $response->assertSessionHas('error');
    }

    public function test_store_feedback_web_is_blocked_for_expired_visit(): void
    {
        $pastVisit = Visit::create([
            'visitor_name' => 'Diana Expired',
            'visitor_company' => 'Past Logistics',
            'visitor_mobile' => '9876543213',
            'organizer_id' => $this->staff->id,
            'visit_date' => today()->subDay(),
        ]);

        $response = $this->actingAs($this->staff)->post(route('visits.feedback.store', $pastVisit), [
            'overall_rating' => 5,
            'comments' => 'Late submission attempt',
            'answers' => $this->getValidAnswers('5'),
        ]);

        $response->assertRedirect(route('shift_queue'));
        $response->assertSessionHas('error');

        // Confirm no feedback was recorded
        $this->assertDatabaseMissing('feedbacks', [
            'visit_id' => $pastVisit->id,
        ]);
    }

    public function test_store_feedback_web_succeeds_for_today_visit(): void
    {
        $todayVisit = Visit::create([
            'visitor_name' => 'Evan Today',
            'visitor_company' => 'Current Co',
            'visitor_mobile' => '9876543214',
            'organizer_id' => $this->staff->id,
            'visit_date' => today(),
        ]);

        $response = $this->actingAs($this->staff)->post(route('visits.feedback.store', $todayVisit), [
            'overall_rating' => 5,
            'comments' => 'Great tour today!',
            'answers' => $this->getValidAnswers('5'),
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('feedbacks', [
            'visit_id' => $todayVisit->id,
            'overall_rating' => 5,
        ]);
    }

    public function test_api_visitor_feedback_blocks_expired_visit(): void
    {
        $pastVisit = Visit::create([
            'visitor_name' => 'Fiona Past',
            'visitor_code' => 'VIS-PAST-01',
            'visitor_company' => 'Yesterday Ltd',
            'visitor_mobile' => '9876543215',
            'organizer_id' => $this->staff->id,
            'visit_date' => today()->subDay(),
        ]);

        $response = $this->postJson('/api/visitor/feedback', [
            'visit_id' => $pastVisit->id,
            'organizer_id' => $this->staff->id,
            'visitor_name' => 'Fiona Past',
            'visitor_mobile' => '9876543215',
            'overall_rating' => 4,
            'answers' => $this->getValidAnswers('4'),
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('expired', strtolower($response->json('message') ?? ''));
    }

    public function test_api_organizer_submit_on_behalf_blocks_expired_visit(): void
    {
        $pastVisit = Visit::create([
            'visitor_name' => 'George Past',
            'visitor_code' => 'VIS-PAST-02',
            'visitor_company' => 'Late Corp',
            'visitor_mobile' => '9876543216',
            'organizer_id' => $this->staff->id,
            'visit_date' => today()->subDays(3),
        ]);

        $token = auth('api')->login($this->staff);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/organizer/visits/{$pastVisit->id}/feedback", [
                'overall_rating' => 4,
                'answers' => $this->getValidAnswers('4'),
            ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('expired', strtolower($response->json('message') ?? ''));
    }

    public function test_visits_index_view_displays_expired_status_for_past_visits(): void
    {
        $pastVisit = Visit::create([
            'visitor_name' => 'Harry Past',
            'visitor_company' => 'Past Industrial',
            'visitor_mobile' => '9876543217',
            'organizer_id' => $this->staff->id,
            'visit_date' => today()->subDay(),
        ]);

        $response = $this->actingAs($this->staff)->get(route('visits.index', ['q' => 'Harry Past']));

        $response->assertOk();
        $response->assertSee('Expired (Day Ended)');
        $response->assertDontSee(route('visits.feedback.create', $pastVisit));
    }
}
