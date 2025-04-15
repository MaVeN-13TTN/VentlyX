<?php

namespace Tests\API;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends ApiTestCase
{
    /**
     * Test user registration
     */
    public function test_user_can_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test_register_' . time() . '@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone_number' => '1234567890'
        ];

        $response = $this->makeUnauthenticatedRequest('POST', '/api/v1/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'created_at'],
                'token',
                'refresh_token',
                'token_type'
            ]);
    }

    /**
     * Test user login
     */
    public function test_user_can_login()
    {
        $password = 'Password123!';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $response = $this->makeUnauthenticatedRequest('POST', '/api/v1/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
                'refresh_token',
                'token_type'
            ]);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_login_with_invalid_credentials_fails()
    {
        $user = User::factory()->create();

        $response = $this->makeUnauthenticatedRequest('POST', '/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid login credentials'
            ]);
    }

    /**
     * Test token validation
     */
    public function test_token_validation()
    {
        $user = $this->createUserWithRole('User');
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email']
            ]);
    }

    /**
     * Test accessing protected endpoint without token
     */
    public function test_protected_endpoint_requires_authentication()
    {
        $response = $this->makeUnauthenticatedRequest('GET', '/api/v1/profile');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /**
     * Test accessing protected endpoint with invalid token
     */
    public function test_invalid_token_is_rejected()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json',
        ])->getJson('/api/v1/profile');

        $response->assertStatus(401);
    }

    /**
     * Test user logout
     */
    public function test_user_can_logout()
    {
        $user = $this->createUserWithRole('User');
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);

        // Note: In Sanctum, tokens are stateless and may still be valid after logout
        // depending on your configuration. This test just verifies the logout endpoint works.
    }
}
