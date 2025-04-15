<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\ApiTestCase;

class LoginTest extends ApiTestCase
{
    /**
     * Test successful login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!')
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ],
                'token',
                'refresh_token',
                'token_type'
            ]);

        // Verify token creation
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class
        ]);
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials()
    {
        // Create a user first
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!')
        ]);

        // Test with incorrect password
        $wrongPasswordData = [
            'email' => 'test@example.com',
            'password' => 'WrongPassword123!'
        ];

        $response = $this->postJson('/api/v1/auth/login', $wrongPasswordData);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid login credentials'
            ]);

        // Test with non-existent email
        $nonExistentEmailData = [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/v1/auth/login', $nonExistentEmailData);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid login credentials'
            ]);
    }

    /**
     * Test login validation errors.
     */
    public function test_login_validation_errors()
    {
        // Test with missing email
        $missingEmailData = [
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/v1/auth/login', $missingEmailData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Test with missing password
        $missingPasswordData = [
            'email' => 'test@example.com'
        ];

        $response = $this->postJson('/api/v1/auth/login', $missingPasswordData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Test with invalid email format
        $invalidEmailData = [
            'email' => 'not-an-email',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/v1/auth/login', $invalidEmailData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user logout functionality.
     */
    public function test_user_can_logout()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);

        // Verify no tokens remain for this user
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class
        ]);
    }

    /**
     * Test that previous tokens are deleted on new login.
     */
    public function test_previous_tokens_deleted_on_login()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!')
        ]);

        // Create a token
        $token1 = $user->createToken('first_token')->plainTextToken;

        // Verify token exists
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);

        // Login again
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200);

        // Extract new token
        $token2 = $response->json('token');

        // Make sure we got a new token
        $this->assertNotEquals($token1, $token2);

        // Check that we have exactly two tokens now (access token and refresh token)
        $this->assertEquals(2, $user->tokens()->count());
    }

    /**
     * Test user profile access.
     */
    public function test_user_can_access_profile()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                    'roles'
                ]
            ]);
    }
}
