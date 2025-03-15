<?php

namespace Tests\Feature\Events;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EventCRUDTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Organizer']);
        Role::create(['name' => 'User']);
    }

    public function test_organizer_can_create_event()
    {
        Storage::fake('public');

        // Create organizer user and attach role
        /** @var User $organizer */
        $organizer = User::factory()->create();
        $organizerRole = Role::where('name', 'Organizer')->first();
        $organizer->roles()->attach($organizerRole);

        $eventData = [
            'title' => 'Test Event',
            'description' => 'Test event description',
            'start_time' => Carbon::now()->addDays(1)->toDateTimeString(),
            'end_time' => Carbon::now()->addDays(1)->addHours(2)->toDateTimeString(),
            'location' => 'Test Location',
            'venue' => 'Test Venue',
            'category' => 'Conference',
            'image' => UploadedFile::fake()->image('event.jpg'),
            'max_capacity' => 100,
            'status' => 'published'
        ];

        $response = $this->actingAs($organizer)
            ->postJson('/api/v1/events', $eventData);

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
                    'image_url',
                    'organizer_id'
                ]
            ]);

        $this->assertDatabaseHas('events', [
            'title' => 'Test Event',
            'organizer_id' => $organizer->id
        ]);

        // Verify image was stored
        $event = Event::where('title', 'Test Event')->first();
        $this->assertTrue(Storage::disk('public')->exists(str_replace('/storage/', '', $event->image_url)));
    }

    public function test_can_retrieve_published_event()
    {
        $event = Event::factory()->create([
            'status' => 'published'
        ]);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $event->id,
                    'title' => $event->title
                ]
            ]);
    }

    public function test_can_list_only_published_events()
    {
        // Create mix of published and draft events
        Event::factory()->count(3)->create(['status' => 'published']);
        Event::factory()->count(2)->create(['status' => 'draft']);

        $response = $this->getJson('/api/v1/events');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_organizer_can_update_own_event()
    {
        // Create organizer user and attach role
        /** @var User $organizer */
        $organizer = User::factory()->create();
        $organizerRole = Role::where('name', 'Organizer')->first();
        $organizer->roles()->attach($organizerRole);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $updateData = [
            'title' => 'Updated Event Title',
            'description' => 'Updated event description'
        ];

        $response = $this->actingAs($organizer)
            ->putJson("/api/v1/events/{$event->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('event.title', 'Updated Event Title');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Event Title'
        ]);
    }

    public function test_organizer_can_delete_own_event()
    {
        // Create organizer user and attach role
        /** @var User $organizer */
        $organizer = User::factory()->create();
        $organizerRole = Role::where('name', 'Organizer')->first();
        $organizer->roles()->attach($organizerRole);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $response = $this->actingAs($organizer)
            ->deleteJson("/api/v1/events/{$event->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
