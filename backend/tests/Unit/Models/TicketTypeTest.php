<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\TicketType;
use App\Models\Event;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_type_can_be_created()
    {
        $event = Event::factory()->create();
        $ticketTypeData = [
            'event_id' => $event->id,
            'name' => 'VIP Ticket',
            'description' => 'VIP access with special perks',
            'price' => 5000,
            'quantity' => 100,
            'max_per_order' => 4,
            'sales_start_date' => Carbon::now(),
            'sales_end_date' => Carbon::now()->addDays(30),
            'tickets_remaining' => 100,
            'status' => 'active'
        ];

        $ticketType = TicketType::create($ticketTypeData);

        $this->assertDatabaseHas('ticket_types', [
            'name' => $ticketTypeData['name'],
            'event_id' => $event->id
        ]);

        $this->assertInstanceOf(TicketType::class, $ticketType);
    }

    public function test_ticket_type_belongs_to_event()
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id
        ]);

        $this->assertInstanceOf(Event::class, $ticketType->event);
        $this->assertEquals($event->id, $ticketType->event->id);
    }

    public function test_ticket_type_has_bookings_relationship()
    {
        $ticketType = TicketType::factory()->create();
        $booking = Booking::factory()->create([
            'ticket_type_id' => $ticketType->id
        ]);

        $this->assertTrue($ticketType->bookings->contains($booking));
        $this->assertInstanceOf(Booking::class, $ticketType->bookings->first());
    }

    public function test_ticket_type_can_check_availability()
    {
        $ticketType = TicketType::factory()->create([
            'quantity' => 10,
            'tickets_remaining' => 5,
            'status' => 'active',
            'sales_start_date' => Carbon::now()->subDay(),
            'sales_end_date' => Carbon::now()->addDays(5)
        ]);

        $this->assertTrue($ticketType->isAvailable());

        // Test when sold out
        $ticketType->update(['tickets_remaining' => 0]);
        $this->assertFalse($ticketType->fresh()->isAvailable());

        // Test when not active
        $ticketType->update(['status' => 'paused', 'tickets_remaining' => 5]);
        $this->assertFalse($ticketType->fresh()->isAvailable());

        // Test when sales ended
        $ticketType->update([
            'status' => 'active',
            'sales_end_date' => Carbon::now()->subDay()
        ]);
        $this->assertFalse($ticketType->fresh()->isAvailable());
    }

    public function test_ticket_type_can_check_if_sold_out()
    {
        $ticketType = TicketType::factory()->create([
            'quantity' => 10,
            'tickets_remaining' => 5
        ]);

        $this->assertFalse($ticketType->isSoldOut());

        $ticketType->update(['tickets_remaining' => 0]);
        $this->assertTrue($ticketType->fresh()->isSoldOut());
    }

    public function test_ticket_type_can_update_tickets_remaining()
    {
        $ticketType = TicketType::factory()->create([
            'quantity' => 100,
            'tickets_remaining' => 100
        ]);

        // Create some confirmed bookings
        Booking::factory()->count(2)->create([
            'ticket_type_id' => $ticketType->id,
            'quantity' => 10,
            'status' => 'confirmed'
        ]);

        // Create a pending booking (shouldn't affect remaining count)
        Booking::factory()->create([
            'ticket_type_id' => $ticketType->id,
            'quantity' => 5,
            'status' => 'pending'
        ]);

        $ticketType->updateTicketsRemaining();

        $this->assertEquals(80, $ticketType->fresh()->tickets_remaining);
    }

    public function test_ticket_type_can_check_sales_period()
    {
        $ticketType = TicketType::factory()->create([
            'sales_start_date' => Carbon::now()->addDay(),
            'sales_end_date' => Carbon::now()->addDays(10)
        ]);

        $this->assertFalse($ticketType->isSalesOpen());

        $ticketType->update([
            'sales_start_date' => Carbon::now()->subDays(2),
            'sales_end_date' => Carbon::now()->addDays(5)
        ]);

        $this->assertTrue($ticketType->fresh()->isSalesOpen());

        $ticketType->update([
            'sales_start_date' => Carbon::now()->subDays(10),
            'sales_end_date' => Carbon::now()->subDay()
        ]);

        $this->assertFalse($ticketType->fresh()->isSalesOpen());
    }

    public function test_ticket_type_scope_active()
    {
        // Create tickets with different statuses
        $activeTicket = TicketType::factory()->create(['status' => 'active']);
        TicketType::factory()->create(['status' => 'paused']);
        TicketType::factory()->create(['status' => 'draft']);

        $activeTickets = TicketType::active()->get();

        $this->assertEquals(1, $activeTickets->count());
        $this->assertTrue($activeTickets->contains($activeTicket));
    }

    public function test_ticket_type_can_check_if_can_be_modified()
    {
        $ticketType = TicketType::factory()->create();

        // Should be modifiable when no bookings exist
        $this->assertTrue($ticketType->canBeModified());

        // Create a confirmed booking
        Booking::factory()->create([
            'ticket_type_id' => $ticketType->id,
            'status' => 'confirmed'
        ]);

        // Should not be modifiable with confirmed bookings
        $this->assertFalse($ticketType->fresh()->canBeModified());

        // Should be modifiable with only pending bookings
        $ticketType = TicketType::factory()->create();
        Booking::factory()->create([
            'ticket_type_id' => $ticketType->id,
            'status' => 'pending'
        ]);

        $this->assertTrue($ticketType->fresh()->canBeModified());
    }
}
