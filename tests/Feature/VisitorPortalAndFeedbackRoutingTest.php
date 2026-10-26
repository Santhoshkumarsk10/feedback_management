<?php

namespace Tests\Feature;

use App\Models\Plant;
use App\Models\Question;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VisitorPortalAndFeedbackRoutingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $staff;
    protected Plant $plant;
    protected Visit $todayVisit;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'staff'], [
            'display_name' => 'Staff Tour Escort',
            'is_system' => true,
        ]);

        $this->plant = Plant::firstOrCreate(['code' => 'PLANT-TEST-ROUTING'], [
            'name' => 'Main Chennai Facility',
            'location' => 'Chembarambakkam, Chennai',
            'is_active' => true,
        ]);

        $this->staff = User::firstOrCreate(['email' => 'ravi.routing@plant.test'], [
            'name' => 'Ravi Kumar',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->todayVisit = Visit::create([
            'visitor_name' => 'Deepak Chopra',
            'visitor_company' => 'Tata Electronics',
            'visitor_mobile' => '9840113614',
            'visitor_code' => 'VIS-2026-ROUTING',
            'purpose' => 'Plant & Facility Tour',
            'organizer_id' => $this->staff->id,
            'plant_id' => $this->plant->id,
            'visit_date' => today(),
        ]);
    }

    public function test_visitor_feedback_home_screen_renders_successfully(): void
    {
        $response = $this->get('/visitor');

        $response->assertStatus(200);
        $response->assertSee('Plant Experience & Visitor Feedback Terminal', false);
        $response->assertSee('Deepak Chopra');
        $response->assertSee('Tata Electronics');
        $response->assertSee('Organizer Portal');
    }

    public function test_visitor_feedback_alias_redirects_to_visitor_home(): void
    {
        $this->get('/visitor-feedback')->assertRedirect(route('visitor.home'));
        $this->get('/visitor-welcome')->assertRedirect(route('visitor.home'));
        $this->get('/feedback-link')->assertRedirect(route('visitor.home'));
    }

    public function test_organizer_portal_and_login_routes_redirect_properly(): void
    {
        // Guest: redirects to login
        $this->get('/organizer/login')->assertRedirect(route('login'));
        $this->get('/organizer/portal')->assertRedirect(route('login'));
        $this->get('/organizer')->assertRedirect(route('login'));

        // Authenticated: redirects to dashboard
        $this->actingAs($this->staff)->get('/organizer/login')->assertRedirect(route('dashboard'));
        $this->actingAs($this->staff)->get('/organizer/portal')->assertRedirect(route('dashboard'));
        $this->actingAs($this->staff)->get('/organizer')->assertRedirect(route('dashboard'));
    }

    public function test_direct_feedback_link_loads_without_admin_prefix(): void
    {
        // Direct link /visits/{id}/feedback/create without /admin prefix
        $response = $this->get("/visits/{$this->todayVisit->id}/feedback/create");

        $response->assertStatus(200);
        $response->assertSee('Deepak Chopra');
        $response->assertSee('Tata Electronics');
        $response->assertSee('Facility & Organizer Selection', false);
    }

    public function test_direct_feedback_link_submission_without_admin_prefix(): void
    {
        $requiredQuestions = Question::active()->where('is_required', true)->get();
        if ($requiredQuestions->isEmpty()) {
            Question::create([
                'question' => 'How was the facility tour quality?',
                'type' => 'rating',
                'section' => 'Quality',
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]);
            $requiredQuestions = Question::active()->where('is_required', true)->get();
        }

        $answers = $requiredQuestions->map(fn ($q) => [
            'question_id' => $q->id,
            'answer' => $q->type === 'rating' ? 5 : 'Yes',
        ])->toArray();

        $payload = [
            'plant_id' => $this->plant->id,
            'organizer_id' => $this->staff->id,
            'overall_rating' => 5,
            'comments' => 'Exceptional facility tour and demonstration.',
            'answers' => $answers,
        ];

        // Submit via direct link /visits/{id}/feedback
        $response = $this->post("/visits/{$this->todayVisit->id}/feedback", $payload);

        $response->assertRedirect(route('visitor.home'));
        $this->assertDatabaseHas('feedbacks', [
            'visit_id' => $this->todayVisit->id,
            'overall_rating' => 5,
            'submitted_mode' => 'direct',
        ]);
    }

    public function test_visitor_lookup_redirects_to_feedback_form_for_today_visit(): void
    {
        $code = $this->todayVisit->refresh()->visitor_code ?: 'VIS-2026-ROUTING';
        $response = $this->get('/visitor/lookup?code=' . urlencode($code));

        $response->assertRedirect("/admin/visits/{$this->todayVisit->id}/feedback/create");
    }
}
