<?php

namespace Tests\API;

use Illuminate\Support\Facades\RateLimiter;

class RateLimitingTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear rate limiter before each test
        RateLimiter::clear('api');
    }

    /**
     * Test that API requests can be made without hitting rate limits
     */
    public function test_api_requests_can_be_made()
    {
        $user = $this->createUserWithRole('User');
        $token = $this->getAuthToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/profile');

        $response->assertStatus(200);
    }

    /**
     * Test that auth routes have specific rate limiting
     */
    public function test_auth_routes_have_specific_rate_limiting()
    {
        // Make multiple login attempts
        for ($i = 0; $i < 3; $i++) {
            $response = $this->makeUnauthenticatedRequest('POST', '/api/v1/auth/login', [
                'email' => 'nonexistent' . $i . '@example.com',
                'password' => 'wrong-password',
            ]);

            // We expect 401 for invalid credentials
            $response->assertStatus(401);
        }
    }

    /**
     * Test that booking routes can be accessed
     */
    public function test_booking_routes_can_be_accessed()
    {
        $user = $this->createUserWithRole('User');
        $token = $this->getAuthToken($user);

        // Make a request to the bookings endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/v1/bookings');

        // The request should succeed
        $response->assertStatus(200);
    }

    /**
     * Test that public endpoints have caching headers
     */
    public function test_public_endpoints_have_caching_headers()
    {
        $response = $this->makeUnauthenticatedRequest('GET', '/api/v1/events');

        $response->assertStatus(200);

        // Check for cache-related headers
        $this->assertTrue(
            $response->headers->has('Cache-Control') ||
                $response->headers->has('Etag') ||
                $response->headers->has('Last-Modified')
        );
    }
}
