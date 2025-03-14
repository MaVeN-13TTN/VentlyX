<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Tests\Feature\ApiTestCase;

class PasswordResetTest extends ApiTestCase
{
    /**
     * Test password reset request with valid email.
     */
    public function test_password_reset_request_with_valid_email()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password reset link has been sent to your email address'
            ]);

        // Check that a reset token was created
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test password reset request with invalid email.
     */
    public function test_password_reset_request_with_invalid_email()
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test password reset completion with valid token.
     */
    public function test_password_reset_completion_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword123!')
        ]);

        // Create a password reset token
        $token = Password::createToken($user);

        // Check the old password hash
        $oldPasswordHash = $user->password;

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password has been successfully reset'
            ]);

        // Reload the user and check that the password was updated
        $user->refresh();
        $this->assertNotEquals($oldPasswordHash, $user->password);
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));

        // Check that the reset token was deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'test@example.com'
        ]);
    }

    /**
     * Test password reset completion with invalid token.
     */
    public function test_password_reset_completion_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Unable to reset password',
                'errors' => ['token' => 'Invalid or expired token']
            ]);
    }

    /**
     * Test password reset completion with validation errors.
     */
    public function test_password_reset_completion_with_validation_errors()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $token = Password::createToken($user);

        // Test with weak password
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Test with mismatched password confirmation
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'DifferentPassword123!'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Test with missing fields
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
