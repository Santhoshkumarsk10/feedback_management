<?php

namespace Tests\Feature;

use App\Models\Plant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PlantManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var User $admin */
        $admin = User::where('role', 'superadmin')->first() ?? User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);
        $this->admin = $admin;
    }

    public function test_admin_can_view_plant_directory_and_filter()
    {
        $response = $this->actingAs($this->admin)->get(route('plants.index'));
        $response->assertStatus(200);
        $response->assertSee('Plant Directory');

        // Valid search query with allowed characters: -, &, space
        $searchRes = $this->actingAs($this->admin)->get(route('plants.index', ['q' => 'Plant-01']));
        $searchRes->assertStatus(200);
    }

    public function test_search_rejects_unsupported_special_characters()
    {
        $response = $this->actingAs($this->admin)->get(route('plants.index', ['q' => '<script>alert(1)</script>']));
        $response->assertSessionHasErrors('q');
    }

    public function test_admin_can_register_plant_with_allowed_special_characters()
    {
        $payload = [
            'code' => 'PLANT-99',
            'name' => 'Plant 99 — Advanced Automation & Robotics Division',
            'location' => 'Plot #42, SIPCOT Industrial Park, Chennai - 600124',
            'contact_email' => 'plant99.ops@shibaura-machine.in',
            'contact_phone' => '9844268199',
            'description' => 'Specialized in 6-axis articulated robotics, injection cell automation, and mould trials.',
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->admin)->post(route('plants.store'), $payload);
        $response->assertRedirect(route('plants.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('plants', [
            'code' => 'PLANT-99',
            'name' => 'Plant 99 — Advanced Automation & Robotics Division',
        ]);
    }

    public function test_plant_code_validation_rejects_spaces_and_disallowed_special_characters()
    {
        // Disallowed special character in code: @
        $res1 = $this->actingAs($this->admin)->post(route('plants.store'), [
            'code' => 'PLANT@01',
            'name' => 'Test Division',
        ]);
        $res1->assertSessionHasErrors('code');

        // Spaces in code
        $res2 = $this->actingAs($this->admin)->post(route('plants.store'), [
            'code' => 'PLANT 01',
            'name' => 'Test Division',
        ]);
        $res2->assertSessionHasErrors('code');

        // Min length violation (< 2 characters)
        $res3 = $this->actingAs($this->admin)->post(route('plants.store'), [
            'code' => 'P',
            'name' => 'Test Division',
        ]);
        $res3->assertSessionHasErrors('code');

        // Max length violation (> 20 characters)
        $res4 = $this->actingAs($this->admin)->post(route('plants.store'), [
            'code' => 'PLANT-VERY-LONG-CODE-12345',
            'name' => 'Test Division',
        ]);
        $res4->assertSessionHasErrors('code');
    }

    public function test_facility_name_rejects_malicious_or_disallowed_special_characters()
    {
        $res = $this->actingAs($this->admin)->post(route('plants.store'), [
            'code' => 'PLANT-88',
            'name' => 'Plant <script>alert(1)</script>',
        ]);
        $res->assertSessionHasErrors('name');

        // Min length violation (< 2 characters)
        $resMin = $this->actingAs($this->admin)->post(route('plants.store'), [
            'code' => 'PLANT-88',
            'name' => 'P',
        ]);
        $resMin->assertSessionHasErrors('name');
    }

    public function test_phone_number_rejects_letters_and_disallowed_characters()
    {
        // Rejects letters
        $res = $this->actingAs($this->admin)->post(route('plants.store'), [
            'code' => 'PLANT-77',
            'name' => 'Plant 77 — Test Facility',
            'contact_phone' => 'phone12345',
        ]);
        $res->assertSessionHasErrors('contact_phone');

        // Rejects non-10 digits (e.g. 8 digits)
        $resShort = $this->actingAs($this->admin)->post(route('plants.store'), [
            'code' => 'PLANT-77',
            'name' => 'Plant 77 — Test Facility',
            'contact_phone' => '98442681',
        ]);
        $resShort->assertSessionHasErrors('contact_phone');

        // Rejects starting with less than 6 (e.g. starting with 5)
        $resStart5 = $this->actingAs($this->admin)->post(route('plants.store'), [
            'code' => 'PLANT-77',
            'name' => 'Plant 77 — Test Facility',
            'contact_phone' => '5844268120',
        ]);
        $resStart5->assertSessionHasErrors('contact_phone');
    }

    public function test_admin_can_update_plant_details()
    {
        $plant = Plant::first();
        $this->assertNotNull($plant);

        $response = $this->actingAs($this->admin)->put(route('plants.update', $plant), [
            'code' => $plant->code,
            'name' => $plant->name . ' (Updated)',
            'location' => $plant->location,
            'contact_email' => $plant->contact_email,
            'contact_phone' => $plant->contact_phone,
            'description' => 'Updated facility description with Japanese engineering.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('plants.index'));
        $response->assertSessionHas('success');
    }
}
