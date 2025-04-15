<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Tests\Feature\ApiTestCase;

class RegisterTest extends ApiTestCase
{
    /**
     * Test successful user registration.
     */
    public function test_user_can_register_successfully()
    {
        // Create a User role if it doesn't exist
        Role::firstOrCreate(['name' => 'User']);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone_number' => '1234567890'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'phone_number',
                    'created_at',
                    'updated_at'
                ],
                'token',
                'refresh_token',
                'token_type'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'phone_number' => '1234567890'
        ]);

        // Check role assignment
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('User'));
    }

    /**
     * Test registration with validation errors.
     */
    public function test_registration_fails_with_invalid_data()
    {
        // Test with invalid email format
        $invalidEmailData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson('/api/v1/auth/register', $invalidEmailData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Test with weak password
        $weakPasswordData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];

        $response = $this->postJson('/api/v1/auth/register', $weakPasswordData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Test with mismatched password confirmation
        $mismatchedPasswordData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!'
        ];

        $response = $this->postJson('/api/v1/auth/register', $mismatchedPasswordData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Test with missing required fields
        $incompleteData = [
            'email' => 'test@example.com'
        ];

        $response = $this->postJson('/api/v1/auth/register', $incompleteData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'password']);
    }

    /**
     * Test registration with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email()
    {
        // Create a user first
        User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $userData = [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
