<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\ApiTestCase;

class EventManagementTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Disable middleware for testing
        $this->withoutMiddleware();
    }

    public function test_users_can_view_published_events()
    {
        // Create published and unpublished events
        Event::factory()->count(3)->published()->create();
        Event::factory()->draft()->create();

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_users_can_view_a_single_event()
    {
        $event = Event::factory()->published()->create([
            'title' => 'Test Event',
            'description' => 'Test Description'
        ]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $event->id,
                    'title' => 'Test Event',
                    'description' => 'Test Description'
                ]
            ]);
    }

    public function test_organizers_can_create_events()
    {
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $this->actingAs($organizer);

        $eventData = [
            'title' => 'New Conference',
            'description' => 'A tech conference',
            'start_time' => now()->addDays(10)->toDateTimeString(),
            'end_time' => now()->addDays(10)->addHours(8)->toDateTimeString(),
            'location' => 'Tech Hub',
            'venue' => 'Conference Center',
            'category' => 'conference',
            'max_capacity' => 500
        ];

        $response = $this->postJson('/api/events', $eventData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'event' => [
                    'id',
                    'title',
                    'description',
                    'start_time',
                    'end_time',
                    'location',
                    'venue',
                    'organizer_id',
                    'category'
                ]
            ]);

        $this->assertDatabaseHas('events', [
            'title' => 'New Conference',
            'organizer_id' => $organizer->id
        ]);
    }

    public function test_regular_users_cannot_create_events()
    {
        $user = User::factory()->create();
        // Add only a regular user role, not organizer
        $user->roles()->attach(Role::firstOrCreate(['name' => 'User']));

        // Create all StoreEventRequest required fields
        $eventData = [
            'title' => 'New Conference',
            'description' => 'A tech conference',
            'location' => 'Tech Hub',
            'venue' => 'Conference Center',
            'category' => 'conference',
            'start_time' => now()->addDays(10)->toDateTimeString(),
            'end_time' => now()->addDays(10)->addHours(8)->toDateTimeString(),
        ];

        $this->actingAs($user);
        // First disable middleware to bypass validation
        $this->withoutMiddleware();
        // Then explicitly test authorization in the request
        $response = $this->postJson('/api/events', $eventData);

        // Assert that even with valid data, a regular user gets 403 forbidden
        $response->assertStatus(403);
    }

    public function test_organizers_can_update_their_events()
    {
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'title' => 'Original Title'
        ]);

        $this->actingAs($organizer);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/events/{$event->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Event updated successfully',
                'event' => [
                    'title' => 'Updated Title',
                    'description' => 'Updated description'
                ]
            ]);
    }

    public function test_organizers_cannot_update_others_events()
    {
        $organizer1 = User::factory()->create();
        $organizer2 = User::factory()->create();
        $organizer1->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));
        $organizer2->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create([
            'organizer_id' => $organizer1->id
        ]);

        $this->actingAs($organizer2);

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Attempted Update'
        ]);

        $response->assertStatus(403);
    }

    public function test_organizers_can_delete_their_events()
    {
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $this->actingAs($organizer);

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Event deleted successfully']);

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_organizers_can_upload_event_images()
    {
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $this->actingAs($organizer);

        $file = UploadedFile::fake()->image('event.jpg');

        // Mock the storage for testing
        Storage::fake('public');

        $response = $this->postJson("/api/events/{$event->id}/image", [
            'image' => $file
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'image_url']);

        // Just validate that the response contains an image URL string
        $this->assertStringContainsString('/storage/events/', $response->json('image_url'));

        // Verify the event was updated with an image URL
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
        ]);

        // Check that the updated event has an image_url that's not empty
        $updatedEvent = Event::find($event->id);
        $this->assertNotNull($updatedEvent->image_url);
    }

    public function test_admin_can_toggle_featured_status()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::firstOrCreate(['name' => 'Admin']));

        $event = Event::factory()->create(['featured' => false]);

        $this->actingAs($admin);

        $response = $this->patchJson("/api/events/{$event->id}/toggle-featured");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Event featured status updated successfully',
                'featured' => true
            ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'featured' => true
        ]);
    }

    public function test_non_admins_cannot_toggle_featured_status()
    {
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create(['featured' => false]);

        $this->actingAs($organizer);

        $response = $this->patchJson("/api/events/{$event->id}/toggle-featured");
        $response->assertStatus(403);
    }

    public function test_can_filter_events_by_status()
    {
        // Create events with different statuses
        Event::factory()->count(2)->published()->create();
        Event::factory()->count(3)->draft()->create();
        Event::factory()->count(1)->create(['status' => 'archived']);

        // Test filtering published events
        $response = $this->getJson('/api/events?status=published');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Test filtering draft events
        $response = $this->getJson('/api/events?status=draft');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        // Test filtering archived events
        $response = $this->getJson('/api/events?status=archived');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_organizers_can_view_their_events()
    {
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        // Create events for this organizer
        Event::factory()->count(3)->create([
            'organizer_id' => $organizer->id
        ]);

        // Create events for other organizers
        Event::factory()->count(2)->create();

        $this->actingAs($organizer);

        $response = $this->getJson('/api/my-events');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
