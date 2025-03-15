<?php

namespace Tests\Feature\Events;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class EventAuthorizationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminRole;
    protected $organizerRole;
    protected $userRole;
    protected $admin;
    protected $organizer;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->adminRole = Role::create(['name' => 'Admin']);
        $this->organizerRole = Role::create(['name' => 'Organizer']);
        $this->userRole = Role::create(['name' => 'User']);

        // Create users
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($this->adminRole);

        $this->organizer = User::factory()->create();
        $this->organizer->roles()->attach($this->organizerRole);

        $this->regularUser = User::factory()->create();
        $this->regularUser->roles()->attach($this->userRole);
    }

    public function test_regular_user_cannot_create_event()
    {
        $eventData = [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_time' => Carbon::now()->addDay(),
            'end_time' => Carbon::now()->addDay()->addHours(2),
            'location' => 'Test Location',
            'venue' => 'Test Venue',
            'category' => 'Conference'
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/v1/events', $eventData);

        $response->assertStatus(403);
    }

    public function test_admin_can_manage_any_event()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id
        ]);

        // Admin can update any event
        $updateResponse = $this->actingAs($this->admin)
            ->putJson("/api/v1/events/{$event->id}", [
                'title' => 'Updated by Admin'
            ]);

        $updateResponse->assertStatus(200);

        // Admin can delete any event
        $deleteResponse = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/events/{$event->id}");

        $deleteResponse->assertStatus(200);
    }

    public function test_organizer_cannot_update_others_events()
    {
        $otherOrganizer = User::factory()->create();
        $otherOrganizer->roles()->attach($this->organizerRole);

        $event = Event::factory()->create([
            'organizer_id' => $otherOrganizer->id
        ]);

        $response = $this->actingAs($this->organizer)
            ->putJson("/api/v1/events/{$event->id}", [
                'title' => 'Attempted Update'
            ]);

        $response->assertStatus(403);
    }

    public function test_organizer_cannot_delete_others_events()
    {
        $otherOrganizer = User::factory()->create();
        $otherOrganizer->roles()->attach($this->organizerRole);

        $event = Event::factory()->create([
            'organizer_id' => $otherOrganizer->id
        ]);

        $response = $this->actingAs($this->organizer)
            ->deleteJson("/api/v1/events/{$event->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_toggle_event_featured_status()
    {
        $event = Event::factory()->create([
            'featured' => false
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/events/{$event->id}/toggle-featured");

        $response->assertStatus(200)
            ->assertJsonPath('featured', true);
    }

    public function test_organizer_cannot_toggle_featured_status()
    {
        $event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'featured' => false
        ]);

        $response = $this->actingAs($this->organizer)
            ->patchJson("/api/v1/events/{$event->id}/toggle-featured");

        $response->assertStatus(403);
    }
}
