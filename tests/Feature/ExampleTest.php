<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));

        $loginResponse = $this->get(route('login'));
        $loginResponse->assertStatus(200);
    }

    public function test_admin_dashboard_and_cms_views_render_with_modular_layout(): void
    {
        $user = \App\Models\User::where('role', 'superadmin')->first() ?? \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();
        if ($user) {
            $this->actingAs($user)->get(route('dashboard'))->assertStatus(200);
            $this->actingAs($user)->get(route('company.edit'))->assertStatus(200);
            $this->actingAs($user)->get(route('banners.index'))->assertStatus(200);
        }
    }
}

