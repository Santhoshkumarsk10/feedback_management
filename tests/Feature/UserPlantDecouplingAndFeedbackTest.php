<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Plant;
use App\Models\Question;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserPlantDecouplingAndFeedbackTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $organizer;
    protected Plant $plant;
    protected Question $question;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::where('role', 'superadmin')->first() ?? User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $this->organizer = User::where('role', 'organizer')->first() ?? User::factory()->create([
            'role' => 'organizer',
            'is_active' => true,
        ]);

        $this->plant = Plant::first() ?? Plant::create([
            'code' => 'PLANT-TEST-01',
            'name' => 'Chennai Machine Tool Facility',
            'location' => 'Chembarambakkam, Chennai',
            'is_active' => true,
        ]);

        $this->question = Question::create([
            'section' => 'Machine Trial & Demonstrations',
            'question' => 'How would you rate the plant equipment and tour guide experience?',
            'type' => 'rating',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_user_cannot_be_assigned_to_plant_in_user_crud(): void
    {
        $role = Role::where('slug', 'organizer')->first() ?? Role::create([
            'name' => 'Tour Organizer',
            'slug' => 'organizer',
            'is_active' => true,
        ]);

        // Creating a user with plant_id in payload must not save plant_id because users table has no plant_id
        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), [
            'name' => 'Murugan Swamy',
            'email' => 'murugan.tour@shibaura-machine.in',
            'mobile' => '9840112233',
            'role_id' => $role->id,
            'plant_id' => $this->plant->id,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('users.index'));
        $createdUser = User::where('email', 'murugan.tour@shibaura-machine.in')->first();
        $this->assertNotNull($createdUser);
        $this->assertFalse(array_key_exists('plant_id', $createdUser->getAttributes()));
    }

    public function test_user_directory_and_form_views_do_not_contain_plant_select(): void
    {
        // 1. User create form has no plant dropdown
        $createResponse = $this->actingAs($this->superAdmin)->get(route('users.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertDontSee('name="plant_id"', false);
        $createResponse->assertDontSee('Assigned Manufacturing Plant');

        // 2. User index table has no Assigned Plant column
        $indexResponse = $this->actingAs($this->superAdmin)->get(route('users.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('<th>Assigned Plant</th>', false);
    }

    private function getValidAnswers(string $rating = '5'): array
    {
        return Question::active()->where('is_required', true)->get()->map(fn ($q) => [
            'question_id' => $q->id,
            'answer' => $q->type === 'rating' ? $rating : 'Yes',
        ])->toArray();
    }

    public function test_feedback_submission_fails_without_plant_and_organizer(): void
    {
        $visit = Visit::create([
            'visitor_name' => 'Senthil Nathan',
            'visitor_company' => 'Brakes India Ltd',
            'visitor_mobile' => '9840998877',
            'visit_date' => today(),
        ]);

        $answers = $this->getValidAnswers('5');

        // 1. Missing plant_id
        $resNoPlant = $this->actingAs($this->superAdmin)->post(route('visits.feedback.store', $visit), [
            'organizer_id' => $this->organizer->id,
            'overall_rating' => 5,
            'comments' => 'Outstanding trial session',
            'answers' => $answers,
        ]);
        $resNoPlant->assertSessionHasErrors('plant_id');

        // 2. Missing organizer_id
        $resNoOrg = $this->actingAs($this->superAdmin)->post(route('visits.feedback.store', $visit), [
            'plant_id' => $this->plant->id,
            'overall_rating' => 5,
            'comments' => 'Outstanding trial session',
            'answers' => $answers,
        ]);
        $resNoOrg->assertSessionHasErrors('organizer_id');
    }

    public function test_feedback_submission_stores_plant_id_on_feedback_and_visit(): void
    {
        $visit = Visit::create([
            'visitor_name' => 'Anand Mahindra Rep',
            'visitor_company' => 'Mahindra & Mahindra',
            'visitor_mobile' => '9840887766',
            'visit_date' => today(),
        ]);

        $answers = $this->getValidAnswers('5');

        $res = $this->actingAs($this->superAdmin)->post(route('visits.feedback.store', $visit), [
            'plant_id' => $this->plant->id,
            'organizer_id' => $this->organizer->id,
            'overall_rating' => 5,
            'comments' => 'Excellent casting witness and machine performance',
            'answers' => $answers,
        ]);

        $res->assertRedirect(route('dashboard'));
        $res->assertSessionHas('success');

        // Confirm feedback record has plant_id and organizer_id
        $this->assertDatabaseHas('feedbacks', [
            'visit_id' => $visit->id,
            'plant_id' => $this->plant->id,
            'organizer_id' => $this->organizer->id,
            'overall_rating' => 5,
        ]);

        // Confirm visit record is updated with plant_id and organizer_id
        $visit->refresh();
        $this->assertEquals($this->plant->id, $visit->plant_id);
        $this->assertEquals($this->organizer->id, $visit->organizer_id);

        // Confirm feedback show view renders plant name
        $feedback = Feedback::where('visit_id', $visit->id)->first();
        $this->assertNotNull($feedback);
        $showRes = $this->actingAs($this->superAdmin)->get(route('feedbacks.show', $feedback));
        $showRes->assertStatus(200);
        $showRes->assertSee($this->plant->name);
        $showRes->assertSee($this->plant->code);
    }
}
