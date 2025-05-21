<?php

namespace Tests\Feature\Analytics;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Role;
use App\Models\SavedEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ApiTestCase;

class UserDashboardTest extends ApiTestCase
{
    use RefreshDatabase;

    protected $user;
    protected $event;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user with the User role
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $this->user = User::factory()->create();
        $this->user->roles()->attach($userRole);

        // Create an event
        $this->event = Event::factory()->create([
            'title' => 'Test Event',
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(7)->addHours(3),
            'location' => 'Test Location',
            'status' => 'published'
        ]);

        // Create a booking for the user
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'total_price' => 100.00,
            'status' => 'confirmed'
        ]);
    }

    /**
     * Test that an unauthenticated user cannot access the dashboard.
     */
    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->getJson('/api/v1/analytics/user-dashboard');

        $response->assertStatus(401);
    }

    /**
     * Test that an authenticated user can access their dashboard.
     */
    public function test_authenticated_user_can_access_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/analytics/user-dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'upcoming_events',
                'recent_bookings',
                'saved_events',
                'stats' => [
                    'total_bookings',
                    'upcoming_events',
                    'total_spent'
                ]
            ]);
    }

    /**
     * Test that the dashboard includes the user's bookings.
     */
    public function test_dashboard_includes_user_bookings()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/analytics/user-dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('stats.total_bookings', 1)
            ->assertJsonPath('recent_bookings.0.id', $this->booking->id)
            ->assertJsonPath('recent_bookings.0.event_title', $this->event->title);
    }

    /**
     * Test that the dashboard includes the user's upcoming events.
     */
    public function test_dashboard_includes_upcoming_events()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/analytics/user-dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('stats.upcoming_events', 1)
            ->assertJsonPath('upcoming_events.0.title', $this->event->title)
            ->assertJsonPath('upcoming_events.0.location', $this->event->location);
    }

    /**
     * Test that the dashboard includes the user's saved events.
     */
    public function test_dashboard_includes_saved_events()
    {
        // Create a saved event for the user
        SavedEvent::create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/analytics/user-dashboard');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'saved_events')
            ->assertJsonPath('saved_events.0.title', $this->event->title);
    }

    /**
     * Test that the dashboard stats are calculated correctly.
     */
    public function test_dashboard_stats_are_calculated_correctly()
    {
        // Create another booking for the user
        Booking::factory()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'total_price' => 50.00,
            'status' => 'confirmed'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/analytics/user-dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('stats.total_bookings', 2);

        // Check that total_spent is approximately 150
        $totalSpent = $response->json('stats.total_spent');
        $this->assertEquals(150, $totalSpent, '', 0.01);
    }
}
