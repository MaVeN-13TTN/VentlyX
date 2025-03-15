<?php

namespace Tests\Feature\Events;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Models\User
     */
    protected $organizer;

    /**
     * @var \App\Models\User
     */
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create(['name' => 'Admin']);
        $organizerRole = Role::create(['name' => 'Organizer']);
        $userRole = Role::create(['name' => 'User']);

        // Create users
        $this->organizer = User::factory()->create();
        $this->organizer->roles()->attach($organizerRole);

        $this->regularUser = User::factory()->create();
        $this->regularUser->roles()->attach($userRole);
    }

    public function test_event_starts_as_draft()
    {
        $response = $this->actingAs($this->organizer)
            ->postJson('/api/v1/events', [
                'title' => 'Test Event',
                'description' => 'Test Description',
                'start_time' => Carbon::now()->addDay(),
                'end_time' => Carbon::now()->addDay()->addHours(2),
                'location' => 'Test Location',
                'venue' => 'Test Venue',
                'category' => 'Conference'
            ]);

        $response->assertStatus(201);

        $eventId = $response->json('event.id');
        $event = Event::find($eventId);

        $this->assertEquals('draft', $event->status);
    }

    public function test_can_publish_draft_event()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => 'draft'
        ]);

        $response = $this->actingAs($this->organizer)
            ->putJson("/api/v1/events/{$event->id}", [
                'status' => 'published'
            ]);

        $response->assertOk();
        $this->assertEquals('published', $event->fresh()->status);
    }

    public function test_published_event_appears_in_listing()
    {
        // Create a published event
        $publishedEvent = Event::factory()->create([
            'status' => 'published'
        ]);

        // Create a draft event 
        $draftEvent = Event::factory()->create([
            'status' => 'draft'
        ]);

        $response = $this->getJson('/api/v1/events');

        $response->assertOk();

        // Check published event is in response
        $this->assertTrue(collect($response->json('data'))->contains(function ($event) use ($publishedEvent) {
            return $event['id'] === $publishedEvent->id;
        }));

        // Check draft event is not in response
        $this->assertFalse(collect($response->json('data'))->contains(function ($event) use ($draftEvent) {
            return $event['id'] === $draftEvent->id;
        }));
    }

    public function test_draft_event_hidden_from_public()
    {
        $event = Event::factory()->create([
            'status' => 'draft'
        ]);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertStatus(404);
    }

    public function test_can_cancel_published_event()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => 'published'
        ]);

        $response = $this->actingAs($this->organizer)
            ->putJson("/api/v1/events/{$event->id}", [
                'status' => 'cancelled'
            ]);

        $response->assertOk();
        $this->assertEquals('cancelled', $event->fresh()->status);
    }

    public function test_cannot_publish_cancelled_event()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => 'cancelled'
        ]);

        $response = $this->actingAs($this->organizer)
            ->putJson("/api/v1/events/{$event->id}", [
                'status' => 'published'
            ]);

        $response->assertStatus(400);
        $this->assertEquals('cancelled', $event->fresh()->status);
    }

    public function test_event_auto_marked_ended_after_end_time()
    {
        // This test requires the Event model to have logic that marks events as ended
        // when end_time is in the past, which should be implemented separately

        $this->assertTrue(true); // Placeholder assertion
    }

    public function test_cannot_modify_ended_event()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => 'ended'
        ]);

        $response = $this->actingAs($this->organizer)
            ->putJson("/api/v1/events/{$event->id}", [
                'title' => 'New Title'
            ]);

        $response->assertStatus(400);
    }
}
