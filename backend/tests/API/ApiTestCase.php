<?php

namespace Tests\API;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a user with the specified role
     *
     * @param string $role Role name ('Organizer', 'User', 'Admin')
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        $roleModel = Role::firstOrCreate(['name' => $role]);

        $defaultAttributes = [
            'name' => $role . ' User',
            'email' => $role . '_' . Str::random(5) . '@example.com',
            'password' => Hash::make('password'),
            'phone_number' => '1234567890',
        ];

        $user = User::factory()->create(array_merge($defaultAttributes, $attributes));
        $user->roles()->attach($roleModel);

        return $user;
    }

    /**
     * Get authentication token for a user
     *
     * @param User $user The user to authenticate
     * @return string The authentication token
     */
    protected function getAuthToken(User $user): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return $response->json('access_token');
    }

    /**
     * Make an authenticated API request
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param User|null $user User to authenticate as (optional)
     * @return \Illuminate\Testing\TestResponse
     */
    protected function makeAuthenticatedRequest(string $method, string $endpoint, array $data = [], ?User $user = null)
    {
        $user = $user ?? $this->createUserWithRole('User');
        $token = $this->getAuthToken($user);

        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->json($method, $endpoint, $data);
    }

    /**
     * Make an unauthenticated API request
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return \Illuminate\Testing\TestResponse
     */
    protected function makeUnauthenticatedRequest(string $method, string $endpoint, array $data = [])
    {
        return $this->withHeaders([
            'Accept' => 'application/json',
        ])->json($method, $endpoint, $data);
    }

    /**
     * Make an authenticated API request as an organizer
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param User|null $organizer Organizer user to authenticate as (optional)
     * @return \Illuminate\Testing\TestResponse
     */
    protected function makeOrganizerRequest(string $method, string $endpoint, array $data = [], ?User $organizer = null)
    {
        $organizer = $organizer ?? $this->createUserWithRole('Organizer');
        return $this->makeAuthenticatedRequest($method, $endpoint, $data, $organizer);
    }

    /**
     * Make an authenticated API request as an admin
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param User|null $admin Admin user to authenticate as (optional)
     * @return \Illuminate\Testing\TestResponse
     */
    protected function makeAdminRequest(string $method, string $endpoint, array $data = [], ?User $admin = null)
    {
        $admin = $admin ?? $this->createUserWithRole('Admin');
        return $this->makeAuthenticatedRequest($method, $endpoint, $data, $admin);
    }
}
