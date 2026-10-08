<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordManagementTest extends TestCase
{
    use DatabaseTransactions;
    public function test_forgot_password_page_loads_successfully()
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Forgot your password?');
        $response->assertSee('Registered Email or Mobile');
    }

    public function test_forgot_password_can_be_requested_with_email()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'testrecovery@plant.test',
            'is_active' => true,
            'role' => 'admin',
        ]);

        $response = $this->post('/forgot-password', [
            'login' => 'testrecovery@plant.test',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'testrecovery@plant.test',
        ]);
    }

    public function test_forgot_password_can_be_requested_with_mobile()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'mobiletest@plant.test',
            'mobile' => '9888877777',
            'is_active' => true,
            'role' => 'organizer',
        ]);

        $response = $this->post('/forgot-password', [
            'login' => '9888877777',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'mobiletest@plant.test',
        ]);
    }

    public function test_reset_password_page_loads_with_token()
    {
        /** @var User $user */
        $user = User::factory()->create(['email' => 'resetpage@plant.test']);
        $token = Password::createToken($user);

        $response = $this->get("/reset-password/{$token}?email=" . urlencode($user->email));
        $response->assertStatus(200);
        $response->assertSee('Set new password');
        $response->assertSee($user->email);
    }

    public function test_password_can_be_reset_with_valid_token()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'validreset@plant.test',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword456!', $user->password));
    }

    public function test_change_password_page_requires_auth()
    {
        $response = $this->get('/admin/change-password');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_change_password_page()
    {
        /** @var User $admin */
        $admin = User::where('role', 'superadmin')->first() ?? User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/change-password');
        $response->assertStatus(200);
        $response->assertSee('Update Password');
        $response->assertSee('Current Password');
    }

    public function test_admin_can_update_password()
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('CurrentPass123!'),
        ]);

        $response = $this->actingAs($admin)->post('/admin/change-password', [
            'current_password' => 'CurrentPass123!',
            'password' => 'BrandNewPass123!',
            'password_confirmation' => 'BrandNewPass123!',
        ]);

        $response->assertSessionHas('success');

        $admin->refresh();
        $this->assertTrue(Hash::check('BrandNewPass123!', $admin->password));
    }

    public function test_admin_change_password_fails_with_wrong_current_password()
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('CorrectPass123!'),
        ]);

        $response = $this->actingAs($admin)->post('/admin/change-password', [
            'current_password' => 'WrongPass999!',
            'password' => 'BrandNewPass123!',
            'password_confirmation' => 'BrandNewPass123!',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_mobile_organizer_forgot_password_and_change_password()
    {
        /** @var User $organizer */
        $organizer = User::factory()->create([
            'role' => 'organizer',
            'is_active' => true,
            'email' => 'mobileorg@plant.test',
            'password' => Hash::make('OldOrgPass123!'),
        ]);

        // API forgot password
        $forgotRes = $this->postJson('/api/forgot-password', [
            'login' => 'mobileorg@plant.test',
        ]);
        $forgotRes->assertStatus(200)->assertJson(['success' => true]);

        // API change password using JWT
        $jwtToken = auth('api')->login($organizer);
        $changeRes = $this->withHeader('Authorization', "Bearer {$jwtToken}")->postJson('/api/change-password', [
            'current_password' => 'OldOrgPass123!',
            'password' => 'NewOrgPass456!',
            'password_confirmation' => 'NewOrgPass456!',
        ]);
        $changeRes->assertStatus(200)->assertJson(['success' => true]);

        $organizer->refresh();
        $this->assertTrue(Hash::check('NewOrgPass456!', $organizer->password));
    }

    public function test_api_forgot_password_and_reset_password()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'apipass@plant.test',
            'role' => 'organizer',
            'is_active' => true,
            'password' => Hash::make('ApiOldPass123!'),
        ]);

        // API forgot password
        $res = $this->postJson('/api/forgot-password', [
            'login' => 'apipass@plant.test',
        ]);
        $res->assertStatus(200)->assertJson(['success' => true]);

        $token = Password::createToken($user);

        // API reset password
        $resetRes = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'apipass@plant.test',
            'password' => 'ApiNewPass456!',
            'password_confirmation' => 'ApiNewPass456!',
        ]);
        $resetRes->assertStatus(200)->assertJson(['success' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('ApiNewPass456!', $user->password));
    }
}
