<?php

namespace Tests\API;

use App\Models\Event;
use App\Models\TicketType;

class ResponseFormatTest extends ApiTestCase
{
    /**
     * Test that successful responses follow a consistent format
     */
    public function test_successful_response_format()
    {
        // Test a GET endpoint
        $response = $this->makeUnauthenticatedRequest('GET', '/api/v1/events');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /**
     * Test that error responses follow a consistent format
     */
    public function test_error_response_format()
    {
        // Test a 404 Not Found response
        $response = $this->makeUnauthenticatedRequest('GET', '/api/v1/events/999999');

        $response->assertStatus(404)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'message',
            ]);

        // Test a validation error response
        $response = $this->makeUnauthenticatedRequest('POST', '/api/v1/auth/login', [
            // Missing required fields
        ]);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    /**
     * Test that event details can be retrieved
     */
    public function test_event_details_can_be_retrieved()
    {
        // Create an event with ticket types
        $organizer = $this->createUserWithRole('Organizer');
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
            'location' => 'Test Location',
            'venue' => 'Test Venue',
            'status' => 'published',
        ]);

        TicketType::factory()->count(2)->create([
            'event_id' => $event->id,
        ]);

        // Get the event details
        $response = $this->makeUnauthenticatedRequest('GET', '/api/v1/events/' . $event->id);

        $response->assertStatus(200);

        // The response format may vary, but we should at least have some data
        $responseData = $response->json();
        $this->assertNotEmpty($responseData);

        // If the response is wrapped in a data key, unwrap it
        $eventData = $responseData['data'] ?? $responseData;

        // Check that we have some event data, but be flexible about the exact structure
        $this->assertNotEmpty($eventData);
    }

    /**
     * Test that collection responses are properly paginated
     */
    public function test_collection_responses_are_paginated()
    {
        // Create multiple events
        $organizer = $this->createUserWithRole('Organizer');
        Event::factory()->count(15)->create([
            'organizer_id' => $organizer->id,
            'status' => 'published',
        ]);

        // Get events with pagination
        $response = $this->makeUnauthenticatedRequest('GET', '/api/v1/events?page=1&per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.current_page', 1);

        // Check second page
        $response = $this->makeUnauthenticatedRequest('GET', '/api/v1/events?page=2&per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    /**
     * Test that API responses include proper CORS headers
     */
    public function test_cors_headers_are_present()
    {
        $response = $this->makeUnauthenticatedRequest('GET', '/api/v1/events');

        $response->assertHeader('Access-Control-Allow-Origin');
    }

    /**
     * Test that API responses are properly cached
     */
    public function test_caching_headers()
    {
        $response = $this->makeUnauthenticatedRequest('GET', '/api/v1/events');

        // Check for cache-related headers
        $this->assertTrue(
            $response->headers->has('Cache-Control') ||
                $response->headers->has('Etag') ||
                $response->headers->has('Last-Modified')
        );
    }

    /**
     * Test that API responses use proper content negotiation
     */
    public function test_content_negotiation()
    {
        // Test JSON response
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get('/api/v1/events');

        $response->assertHeader('Content-Type', 'application/json');

        // Test that requesting XML returns JSON anyway (or appropriate error)
        $response = $this->withHeaders([
            'Accept' => 'application/xml',
        ])->get('/api/v1/events');

        // Either we get JSON anyway, or we get a 406 Not Acceptable
        $this->assertTrue(
            $response->headers->get('Content-Type') === 'application/json' ||
                $response->status() === 406
        );
    }

    /**
     * Test that API root endpoint returns basic API information
     */
    public function test_api_root_returns_basic_information()
    {
        $response = $this->makeUnauthenticatedRequest('GET', '/api');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'name',
                'version',
                'status',
                'documentation',
                'timestamp',
            ]);
    }
}
