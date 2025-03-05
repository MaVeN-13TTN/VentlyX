<?php

namespace Tests\Feature\TicketTypes;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Role;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ApiTestCase;

class TicketTypeManagementTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable middleware for testing
        $this->withoutMiddleware();
    }

    /**
     * Test users can view ticket types for an event
     */
    public function test_users_can_view_ticket_types_for_event()
    {
        $event = Event::factory()->published()->create();
        $ticketTypes = TicketType::factory()->count(3)->create([
            'event_id' => $event->id
        ]);

        $response = $this->getJson('/api/events/' . $event->id . '/ticket-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ticket_types' => [
                    '*' => [
                        'id',
                        'name',
                        'price'
                    ]
                ]
            ])
            ->assertJsonCount(3, 'ticket_types');
    }

    /**
     * Test users can view a specific ticket type
     */
    public function test_users_can_view_a_specific_ticket_type()
    {
        $event = Event::factory()->published()->create();
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'VIP Ticket',
            'price' => 100.00
        ]);

        $response = $this->getJson('/api/events/' . $event->id . '/ticket-types/' . $ticketType->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ticket_type' => [
                    'id',
                    'name',
                    'price'
                ]
            ])
            ->assertJson([
                'ticket_type' => [
                    'id' => $ticketType->id,
                    'name' => 'VIP Ticket',
                    'price' => 100.00
                ]
            ]);
    }

    /**
     * Test checking ticket availability
     */
    public function test_users_can_check_ticket_availability()
    {
        $event = Event::factory()->published()->create();
        $quantity = 50;
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'quantity' => $quantity,
            'tickets_remaining' => $quantity,
            'name' => 'Regular Ticket',
            'status' => 'active'
        ]);

        $response = $this->getJson('/api/events/' . $event->id . '/ticket-types/availability');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'availability' => [
                    '*' => [
                        'id',
                        'name',
                        'available',
                        'remaining',
                        'price'
                    ]
                ]
            ]);
    }

    public function test_organizers_can_create_ticket_types()
    {
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $this->actingAs($organizer);

        $ticketData = [
            'name' => 'VIP Pass',
            'price' => 150.00,
            'quantity' => 100,
            'description' => 'Premium VIP experience',
            'max_per_order' => 4
        ];

        $response = $this->postJson("/api/events/{$event->id}/ticket-types", $ticketData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'ticket_type' => [
                    'id',
                    'name',
                    'price',
                    'quantity',
                    'description',
                    'max_per_order',
                    'event_id'
                ]
            ]);

        $this->assertDatabaseHas('ticket_types', [
            'event_id' => $event->id,
            'name' => 'VIP Pass',
            'price' => 150.00,
            'quantity' => 100
        ]);
    }

    public function test_non_organizers_cannot_create_ticket_types()
    {
        $regularUser = User::factory()->create();
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $this->actingAs($regularUser);

        $ticketData = [
            'name' => 'Regular Ticket',
            'price' => 50.00,
            'quantity' => 100
        ];

        $response = $this->postJson("/api/events/{$event->id}/ticket-types", $ticketData);
        $response->assertStatus(403);
    }

    public function test_organizers_can_update_their_ticket_types()
    {
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'name' => 'Early Bird',
            'price' => 50.00,
            'quantity' => 100
        ]);

        $this->actingAs($organizer);

        $updateData = [
            'name' => 'Early Bird Special',
            'price' => 60.00,
            'quantity' => 150
        ];

        $response = $this->putJson("/api/events/{$event->id}/ticket-types/{$ticketType->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Ticket type updated successfully',
                'ticket_type' => [
                    'name' => 'Early Bird Special',
                    'price' => 60.00,
                    'quantity' => 150
                ]
            ]);
    }

    public function test_organizers_cannot_update_other_events_ticket_types()
    {
        $organizer1 = User::factory()->create();
        $organizer2 = User::factory()->create();
        $organizer1->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));
        $organizer2->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create([
            'organizer_id' => $organizer1->id
        ]);

        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id
        ]);

        $this->actingAs($organizer2);

        $updateData = [
            'name' => 'Modified Ticket',
            'price' => 75.00
        ];

        $response = $this->putJson("/api/events/{$event->id}/ticket-types/{$ticketType->id}", $updateData);
        $response->assertStatus(403);
    }

    public function test_organizers_can_delete_their_ticket_types()
    {
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id
        ]);

        $this->actingAs($organizer);

        $response = $this->deleteJson("/api/events/{$event->id}/ticket-types/{$ticketType->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Ticket type deleted successfully']);

        $this->assertDatabaseMissing('ticket_types', ['id' => $ticketType->id]);
    }

    public function test_organizers_cannot_delete_ticket_types_with_bookings()
    {
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id
        ]);

        // Create a booking for this ticket type
        Booking::factory()->create([
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'status' => 'confirmed'
        ]);

        $this->actingAs($organizer);

        $response = $this->deleteJson("/api/events/{$event->id}/ticket-types/{$ticketType->id}");

        $response->assertStatus(400)
            ->assertJsonStructure(['message', 'bookings_count']);

        $this->assertDatabaseHas('ticket_types', ['id' => $ticketType->id]);
    }
}
