<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Plant;
use App\Models\Question;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ValidationAndPhoneRuleTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $organizer;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var User $admin */
        $admin = User::where('role', 'superadmin')->first() ?? User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);
        $this->admin = $admin;

        /** @var User $organizer */
        $organizer = User::where('role', 'organizer')->first() ?? User::factory()->create([
            'role' => 'organizer',
            'is_active' => true,
        ]);
        $this->organizer = $organizer;
    }

    public function test_user_creation_enforces_10_digit_phone_starting_above_6()
    {
        $role = Role::firstOrCreate(['slug' => 'supervisor'], ['name' => 'Supervisor', 'is_active' => true]);

        // Valid phone starting with 9
        $validPayload = [
            'name' => 'Karthik Raja',
            'email' => 'karthik.raja@shibaura-machine.in',
            'mobile' => '9840123456',
            'role_id' => $role->id,
            'department' => 'Tooling & Trials',
            'password' => 'Secret123!',
            'is_active' => '1',
        ];
        $res = $this->actingAs($this->admin)->post(route('users.store'), $validPayload);
        $res->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'karthik.raja@shibaura-machine.in', 'mobile' => '9840123456']);

        // Invalid: Starts with 5 (must start with 6-9)
        $invalidStart = $validPayload;
        $invalidStart['email'] = 'test5@shibaura.in';
        $invalidStart['mobile'] = '5840123456';
        $res = $this->actingAs($this->admin)->post(route('users.store'), $invalidStart);
        $res->assertSessionHasErrors('mobile');

        // Invalid: 9 digits
        $invalidShort = $validPayload;
        $invalidShort['email'] = 'testshort@shibaura.in';
        $invalidShort['mobile'] = '984012345';
        $res = $this->actingAs($this->admin)->post(route('users.store'), $invalidShort);
        $res->assertSessionHasErrors('mobile');

        // Invalid: 11 digits
        $invalidLong = $validPayload;
        $invalidLong['email'] = 'testlong@shibaura.in';
        $invalidLong['mobile'] = '98401234567';
        $res = $this->actingAs($this->admin)->post(route('users.store'), $invalidLong);
        $res->assertSessionHasErrors('mobile');

        // Invalid: Special characters in mobile
        $invalidChar = $validPayload;
        $invalidChar['email'] = 'testchar@shibaura.in';
        $invalidChar['mobile'] = '98401-23456';
        $res = $this->actingAs($this->admin)->post(route('users.store'), $invalidChar);
        $res->assertSessionHasErrors('mobile');
    }

    public function test_user_creation_rejects_disallowed_special_characters_in_name()
    {
        $role = Role::first();
        $payload = [
            'name' => '<script>alert("hack")</script>',
            'email' => 'badname@shibaura.in',
            'mobile' => '9840123499',
            'role_id' => $role->id,
            'department' => 'Testing',
            'password' => 'Secret123!',
        ];

        $res = $this->actingAs($this->admin)->post(route('users.store'), $payload);
        $res->assertSessionHasErrors('name');
    }

    public function test_company_profile_enforces_10_digit_phone_and_safe_characters()
    {
        $payload = [
            'name' => 'Shibaura Machine India Pvt. Ltd.',
            'phone' => '9844268100',
            'alter_phone' => '8844268200',
            'pincode' => '600123',
            'address' => 'No. 65, Industrial Complex, Ambattur',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
        ];

        $res = $this->actingAs($this->admin)->put(route('company.update'), $payload);
        $res->assertSessionHasNoErrors();
        $res->assertRedirect(route('company.edit'));

        // Invalid phone starting with 4
        $invalidPhone = $payload;
        $invalidPhone['phone'] = '4426812000';
        $res = $this->actingAs($this->admin)->put(route('company.update'), $invalidPhone);
        $res->assertSessionHasErrors('phone');

        // Invalid pincode (not 6 digits)
        $invalidPin = $payload;
        $invalidPin['pincode'] = '60012';
        $res = $this->actingAs($this->admin)->put(route('company.update'), $invalidPin);
        $res->assertSessionHasErrors('pincode');

        // Invalid special characters in name
        $invalidName = $payload;
        $invalidName['name'] = 'Shibaura <Company> {Test}';
        $res = $this->actingAs($this->admin)->put(route('company.update'), $invalidName);
        $res->assertSessionHasErrors('name');
    }

    public function test_role_creation_validation_rules()
    {
        // Valid role
        $res = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'Safety & Audit Lead',
            'slug' => 'safety_audit_lead',
            'description' => 'Oversees safety protocols during customer factory walkthroughs.',
            'is_active' => '1',
        ]);
        $res->assertSessionHasNoErrors();
        $this->assertDatabaseHas('roles', ['slug' => 'safety_audit_lead']);

        // Invalid slug with disallowed special characters like @ or $
        $res = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'Invalid Slug Role',
            'slug' => 'invalid@slug$here',
        ]);
        $res->assertSessionHasErrors('slug');

        // Invalid name too short (min: 2)
        $res = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'A',
            'slug' => 'short_name',
        ]);
        $res->assertSessionHasErrors('name');
    }

    public function test_question_creation_validation_rules()
    {
        // Valid question
        $res = $this->actingAs($this->admin)->post(route('questions.store'), [
            'section' => 'Section 1 — Experience',
            'question' => 'How satisfied are you with our machine demonstration?',
            'type' => 'rating',
            'sort_order' => 1,
            'is_required' => '1',
            'is_active' => '1',
        ]);
        $res->assertSessionHasNoErrors();

        // Disallowed special chars in question
        $res = $this->actingAs($this->admin)->post(route('questions.store'), [
            'section' => 'Section 1 — Experience',
            'question' => '<script>alert("hack")</script>',
            'type' => 'rating',
        ]);
        $res->assertSessionHasErrors('question');
    }

    public function test_visitor_submission_enforces_10_digit_phone_and_clean_data()
    {
        $question = Question::active()->first() ?? Question::create([
            'question' => 'Overall Experience',
            'type' => 'rating',
            'sort_order' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);

        $activeQuestions = Question::active()->where('is_required', true)->get();
        if ($activeQuestions->isEmpty()) {
            $activeQuestions = collect([$question]);
        }
        $answers = $activeQuestions->map(fn($q) => [
            'question_id' => $q->id,
            'answer' => $q->type === 'rating' ? '5' : 'Great demonstration',
        ])->values()->all();

        // Valid visitor feedback
        $validPayload = [
            'organizer_id' => $this->organizer->id,
            'visitor_name' => 'Mr. Sundar Rajan',
            'visitor_company' => 'Sundaram Fasteners Ltd.',
            'visitor_mobile' => '9841234567',
            'visitor_email' => 'sundar@sundaram.in',
            'visitor_designation' => 'Plant Head — Operations',
            'overall_rating' => 5,
            'answers' => $answers,
        ];

        $res = $this->postJson(route('mobile.feedback'), $validPayload);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        // Invalid visitor phone starting with 3
        $invalidPhone = $validPayload;
        $invalidPhone['visitor_mobile'] = '3841234567';
        $res = $this->postJson(route('mobile.feedback'), $invalidPhone);
        $res->assertStatus(422);
        $res->assertJsonValidationErrors('visitor_mobile');

        // Invalid visitor name containing disallowed symbols
        $invalidName = $validPayload;
        $invalidName['visitor_name'] = 'Sundar <CEO> & Team';
        $res = $this->postJson(route('mobile.feedback'), $invalidName);
        $res->assertStatus(422);
        $res->assertJsonValidationErrors('visitor_name');
    }

    public function test_banner_validation_rules()
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $makeFile = fn() => \Illuminate\Http\UploadedFile::fake()->image('banner.png', 800, 450);

        // Valid banner with headline punctuation
        $res = $this->actingAs($this->admin)->post(route('banners.store'), [
            'title' => '100% Japanese Engineering & Innovation!',
            'subtitle' => 'Precision, Productivity & Reliability: Guaranteed (2026 Edition)',
            'image' => $makeFile(),
            'target' => 'all',
            'link_url' => 'https://www.shibaura-machine.co.in/machines',
            'sort_order' => 5,
            'is_active' => '1',
        ]);
        $res->assertSessionHasNoErrors();
        $this->assertDatabaseHas('banners', ['title' => '100% Japanese Engineering & Innovation!']);

        // Invalid title with disallowed characters (< >)
        $res = $this->actingAs($this->admin)->post(route('banners.store'), [
            'title' => 'Bad <Headline> Alert',
            'image' => $makeFile(),
            'target' => 'all',
            'sort_order' => 1,
        ]);
        $res->assertSessionHasErrors('title');

        // Invalid title too short (min: 2)
        $res = $this->actingAs($this->admin)->post(route('banners.store'), [
            'title' => 'X',
            'image' => $makeFile(),
            'target' => 'all',
            'sort_order' => 1,
        ]);
        $res->assertSessionHasErrors('title');

        // Invalid sort order out of bounds (> 9999)
        $res = $this->actingAs($this->admin)->post(route('banners.store'), [
            'title' => 'Valid Headline',
            'image' => $makeFile(),
            'target' => 'all',
            'sort_order' => 10000,
        ]);
        $res->assertSessionHasErrors('sort_order');
    }

    public function test_auth_login_validation_rules()
    {
        // Rejects disallowed characters in login credential
        $res = $this->post(route('login'), [
            'login' => 'super<admin>@test.com',
            'password' => 'secret123',
        ]);
        $res->assertSessionHasErrors('login');

        // Rejects login shorter than 3 characters
        $res = $this->post(route('login'), [
            'login' => 'ab',
            'password' => 'secret123',
        ]);
        $res->assertSessionHasErrors('login');
    }

    public function test_change_password_validation_rules()
    {
        // New password too short (min: 8)
        $res = $this->actingAs($this->admin)->post(route('password.change.update'), [
            'current_password' => 'password',
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]);
        $res->assertSessionHasErrors('password');

        // New password confirmation does not match
        $res = $this->actingAs($this->admin)->post(route('password.change.update'), [
            'current_password' => 'password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);
        $res->assertSessionHasErrors('password');

        // New password same as current password
        $res = $this->actingAs($this->admin)->post(route('password.change.update'), [
            'current_password' => 'password',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $res->assertSessionHasErrors('password');
    }

    public function test_strict_email_format_validation_across_models()
    {
        $role = Role::firstOrCreate(['slug' => 'guide'], ['name' => 'Tour Guide', 'is_active' => true]);

        // 1. User email without valid domain TLD
        $res = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Valid Name',
            'email' => 'invalid-email@nodomain',
            'mobile' => '9840123999',
            'role_id' => $role->id,
            'password' => 'Secret123!',
        ]);
        $res->assertSessionHasErrors('email');

        // 2. User email with valid domain TLD
        $res = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Valid User',
            'email' => 'valid.user@company.com',
            'mobile' => '9840123998',
            'role_id' => $role->id,
            'password' => 'Secret123!',
        ]);
        $res->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'valid.user@company.com']);

        // 3. Plant email validation
        $res = $this->actingAs($this->admin)->post(route('plants.store'), [
            'code' => 'PLANT-TEST',
            'name' => 'Test Plant',
            'contact_email' => 'bademail@notld',
            'contact_phone' => '9840123997',
        ]);
        $res->assertSessionHasErrors('contact_email');

        // 4. Company email validation
        $res = $this->actingAs($this->admin)->put(route('company.update'), [
            'name' => 'Shibaura Machine India',
            'email' => 'badcompany@domain',
            'phone' => '9840123996',
        ]);
        $res->assertSessionHasErrors('email');

        // 5. Visitor submission email validation
        $res = $this->postJson(route('mobile.feedback'), [
            'organizer_id' => $this->organizer->id,
            'visitor_name' => 'Corporate Visitor',
            'visitor_company' => 'Honda Cars',
            'visitor_mobile' => '9840123995',
            'visitor_email' => 'visitor@invaliddomain',
            'overall_rating' => 5,
        ]);
        $res->assertStatus(422);
        $res->assertJsonValidationErrors('visitor_email');

        // 6. Forgot password with invalid email domain format (as guest)
        auth()->logout();
        $res = $this->post(route('password.email'), [
            'login' => 'someone@invaliddomain',
        ]);
        $res->assertSessionHasErrors('login');

        // 7. Login with invalid email domain format (as guest)
        $res = $this->post(route('login'), [
            'login' => 'bademail@notld',
            'password' => 'secret123',
        ]);
        $res->assertSessionHasErrors('login');
    }
}
