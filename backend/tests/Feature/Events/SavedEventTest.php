<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\Role;
use App\Models\SavedEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ApiTestCase;

class SavedEventTest extends ApiTestCase
{
    use RefreshDatabase;

    protected $user;
    protected $event;

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
    }

    /**
     * Test that an unauthenticated user cannot save an event.
     */
    public function test_unauthenticated_user_cannot_save_event()
    {
        $response = $this->postJson("/api/v1/events/{$this->event->id}/save");

        $response->assertStatus(401);
    }

    /**
     * Test that an authenticated user can save an event.
     */
    public function test_authenticated_user_can_save_event()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/events/{$this->event->id}/save");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Event saved successfully'
            ]);

        $this->assertDatabaseHas('saved_events', [
            'user_id' => $this->user->id,
            'event_id' => $this->event->id
        ]);
    }

    /**
     * Test that a user cannot save the same event twice.
     */
    public function test_user_cannot_save_same_event_twice()
    {
        // Save the event first
        SavedEvent::create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/events/{$this->event->id}/save");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Event already saved'
            ]);

        // Check that only one record exists
        $this->assertEquals(1, SavedEvent::where([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id
        ])->count());
    }

    /**
     * Test that an authenticated user can remove a saved event.
     */
    public function test_authenticated_user_can_remove_saved_event()
    {
        // Save the event first
        SavedEvent::create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/events/{$this->event->id}/save");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Event removed from saved events'
            ]);

        $this->assertDatabaseMissing('saved_events', [
            'user_id' => $this->user->id,
            'event_id' => $this->event->id
        ]);
    }

    /**
     * Test that a user cannot remove an event that is not saved.
     */
    public function test_user_cannot_remove_unsaved_event()
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/events/{$this->event->id}/save");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Event not found in saved events'
            ]);
    }

    /**
     * Test that an authenticated user can get their saved events.
     */
    public function test_authenticated_user_can_get_saved_events()
    {
        // Save multiple events
        SavedEvent::create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id
        ]);

        $event2 = Event::factory()->create([
            'title' => 'Another Event',
            'status' => 'published'
        ]);

        SavedEvent::create([
            'user_id' => $this->user->id,
            'event_id' => $event2->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/saved-events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'saved_events' => [
                    '*' => [
                        'id',
                        'title',
                        'start_time',
                        'end_time',
                        'location'
                    ]
                ]
            ])
            ->assertJsonCount(2, 'saved_events')
            ->assertJsonPath('saved_events.0.title', $this->event->title)
            ->assertJsonPath('saved_events.1.title', $event2->title);
    }

    /**
     * Test that a user only sees their own saved events.
     */
    public function test_user_only_sees_their_own_saved_events()
    {
        // Create another user and save an event for them
        $anotherUser = User::factory()->create();

        SavedEvent::create([
            'user_id' => $anotherUser->id,
            'event_id' => $this->event->id
        ]);

        // Save a different event for the main test user
        $event2 = Event::factory()->create([
            'title' => 'User Event',
            'status' => 'published'
        ]);

        SavedEvent::create([
            'user_id' => $this->user->id,
            'event_id' => $event2->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/saved-events');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'saved_events')
            ->assertJsonPath('saved_events.0.title', $event2->title);
    }
}
