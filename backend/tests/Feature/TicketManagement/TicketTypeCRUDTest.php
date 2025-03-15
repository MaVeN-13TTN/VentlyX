<?php

namespace Tests\Feature\TicketManagement;

use App\Models\Event;
use App\Models\Role;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $adminRole;
    protected $organizerRole;
    protected $userRole;
    protected $admin;
    protected $organizer;
    protected $regularUser;
    protected $event;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->adminRole = Role::create(['name' => 'Admin']);
        $this->organizerRole = Role::create(['name' => 'Organizer']);
        $this->userRole = Role::create(['name' => 'User']);

        // Create users with respective roles
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($this->adminRole);

        $this->organizer = User::factory()->create();
        $this->organizer->roles()->attach($this->organizerRole);

        $this->regularUser = User::factory()->create();
        $this->regularUser->roles()->attach($this->userRole);

        // Create an event owned by the organizer
        $this->event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => 'published'
        ]);
    }

    public function test_organizer_can_create_ticket_type_for_own_event()
    {
        $ticketTypeData = [
            'name' => 'VIP Ticket',
            'description' => 'VIP access with special perks',
            'price' => 5000,
            'quantity' => 100,
            'max_per_order' => 4,
            'sales_start_date' => now()->addDay()->toDateTimeString(),
            'sales_end_date' => now()->addDays(30)->toDateTimeString()
        ];

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/events/{$this->event->id}/ticket-types", $ticketTypeData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'ticket_type' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'quantity',
                    'tickets_remaining',
                    'status',
                    'event_id'
                ]
            ]);

        $this->assertDatabaseHas('ticket_types', [
            'event_id' => $this->event->id,
            'name' => 'VIP Ticket',
            'price' => 5000,
            'quantity' => 100
        ]);

        // Verify tickets_remaining equals quantity
        $ticketType = TicketType::where('event_id', $this->event->id)
            ->where('name', 'VIP Ticket')
            ->first();
        $this->assertEquals($ticketType->quantity, $ticketType->tickets_remaining);
    }

    public function test_admin_can_create_ticket_type_for_any_event()
    {
        $ticketTypeData = [
            'name' => 'Admin Created Ticket',
            'price' => 2500,
            'quantity' => 50,
            'description' => 'Created by admin'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/events/{$this->event->id}/ticket-types", $ticketTypeData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ticket_types', [
            'event_id' => $this->event->id,
            'name' => 'Admin Created Ticket'
        ]);
    }

    public function test_regular_user_cannot_create_ticket_type()
    {
        $ticketTypeData = [
            'name' => 'Unauthorized Ticket',
            'price' => 1000,
            'quantity' => 25
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson("/api/v1/events/{$this->event->id}/ticket-types", $ticketTypeData);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('ticket_types', [
            'name' => 'Unauthorized Ticket'
        ]);
    }

    public function test_validation_errors_when_creating_ticket_type()
    {
        $invalidData = [
            'name' => '', // Empty name
            'price' => -50, // Negative price
            'quantity' => 0, // Zero quantity
            'sales_start_date' => now()->addDay()->toDateTimeString(),
            'sales_end_date' => now()->toDateTimeString() // End date before start date
        ];

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/events/{$this->event->id}/ticket-types", $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'quantity', 'sales_end_date']);
    }

    public function test_can_list_ticket_types_for_event()
    {
        // Create some ticket types for the event
        $ticketType1 = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'Regular Ticket'
        ]);

        $ticketType2 = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'VIP Ticket'
        ]);

        $response = $this->getJson("/api/v1/events/{$this->event->id}/ticket-types");

        $response->assertStatus(200)
            ->assertJsonStructure(['ticket_types'])
            ->assertJsonCount(2, 'ticket_types');

        $responseData = $response->json('ticket_types');
        $this->assertTrue(collect($responseData)->contains('id', $ticketType1->id));
        $this->assertTrue(collect($responseData)->contains('id', $ticketType2->id));
    }

    public function test_can_get_specific_ticket_type()
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'Premium Ticket',
            'description' => 'Premium ticket description',
            'price' => 7500
        ]);

        $response = $this->getJson("/api/v1/events/{$this->event->id}/ticket-types/{$ticketType->id}");

        $response->assertStatus(200)
            ->assertJson([
                'ticket_type' => [
                    'id' => $ticketType->id,
                    'name' => 'Premium Ticket',
                    'description' => 'Premium ticket description',
                    'price' => '7500.00', // Assert as string due to decimal casting
                    'event_id' => $this->event->id
                ]
            ]);
    }

    public function test_organizer_can_update_own_ticket_type()
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'Original Ticket',
            'price' => 1000,
            'quantity' => 50
        ]);

        $updateData = [
            'name' => 'Updated Ticket Name',
            'description' => 'Updated description',
            'price' => 1500
        ];

        $response = $this->actingAs($this->organizer)
            ->putJson("/api/v1/events/{$this->event->id}/ticket-types/{$ticketType->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Ticket type updated successfully',
                'ticket_type' => [
                    'id' => $ticketType->id,
                    'name' => 'Updated Ticket Name',
                    'description' => 'Updated description',
                    'price' => '1500.00' // Assert as string due to decimal casting
                ]
            ]);

        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType->id,
            'name' => 'Updated Ticket Name'
        ]);
    }

    public function test_organizer_cannot_update_another_organizers_ticket_type()
    {
        // Create another organizer with their own event and ticket type
        $anotherOrganizer = User::factory()->create();
        $anotherOrganizer->roles()->attach($this->organizerRole);

        $anotherEvent = Event::factory()->create([
            'organizer_id' => $anotherOrganizer->id
        ]);

        $anotherTicketType = TicketType::factory()->create([
            'event_id' => $anotherEvent->id
        ]);

        $updateData = [
            'name' => 'Unauthorized Update'
        ];

        $response = $this->actingAs($this->organizer)
            ->putJson("/api/v1/events/{$anotherEvent->id}/ticket-types/{$anotherTicketType->id}", $updateData);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('ticket_types', [
            'id' => $anotherTicketType->id,
            'name' => 'Unauthorized Update'
        ]);
    }

    public function test_organizer_can_delete_ticket_type_with_no_bookings()
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id
        ]);

        $response = $this->actingAs($this->organizer)
            ->deleteJson("/api/v1/events/{$this->event->id}/ticket-types/{$ticketType->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Ticket type deleted successfully'
            ]);

        $this->assertDatabaseMissing('ticket_types', [
            'id' => $ticketType->id
        ]);
    }

    public function test_bulk_create_ticket_types()
    {
        $bulkData = [
            'ticket_types' => [
                [
                    'name' => 'Regular Ticket',
                    'price' => 1000,
                    'quantity' => 200,
                    'description' => 'Standard entry'
                ],
                [
                    'name' => 'VIP Ticket',
                    'price' => 5000,
                    'quantity' => 50,
                    'description' => 'VIP entry with perks'
                ]
            ]
        ];

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/events/{$this->event->id}/ticket-types/bulk", $bulkData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'ticket_types'
            ])
            ->assertJsonCount(2, 'ticket_types');

        $this->assertDatabaseHas('ticket_types', [
            'event_id' => $this->event->id,
            'name' => 'Regular Ticket'
        ]);

        $this->assertDatabaseHas('ticket_types', [
            'event_id' => $this->event->id,
            'name' => 'VIP Ticket'
        ]);
    }

    public function test_bulk_update_ticket_types()
    {
        // Create ticket types to update
        $ticketType1 = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'Ticket One',
            'price' => 1000
        ]);

        $ticketType2 = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'Ticket Two',
            'price' => 2000
        ]);

        $bulkUpdateData = [
            'ticket_types' => [
                [
                    'id' => $ticketType1->id,
                    'name' => 'Updated Ticket One',
                    'price' => 1200,
                    'quantity' => 100
                ],
                [
                    'id' => $ticketType2->id,
                    'name' => 'Updated Ticket Two',
                    'price' => 2500,
                    'quantity' => 50
                ]
            ]
        ];

        $response = $this->actingAs($this->organizer)
            ->putJson("/api/v1/events/{$this->event->id}/ticket-types/bulk", $bulkUpdateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Ticket types updated successfully'
            ]);

        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType1->id,
            'name' => 'Updated Ticket One'
        ]);

        $this->assertDatabaseHas('ticket_types', [
            'id' => $ticketType2->id,
            'name' => 'Updated Ticket Two'
        ]);
    }
}
