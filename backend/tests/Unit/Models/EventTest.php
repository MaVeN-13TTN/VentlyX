<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Event;
use App\Models\User;
use App\Models\TicketType;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_can_be_created()
    {
        $organizer = User::factory()->create();
        $eventData = [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(2),
            'location' => 'Test Location',
            'venue' => 'Test Venue',
            'category' => 'Conference',
            'status' => 'published',
            'organizer_id' => $organizer->id,
        ];

        $event = Event::create($eventData);

        $this->assertDatabaseHas('events', [
            'title' => $eventData['title'],
            'organizer_id' => $organizer->id
        ]);

        $this->assertInstanceOf(Event::class, $event);
    }

    public function test_event_belongs_to_organizer()
    {
        $organizer = User::factory()->create();
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $this->assertInstanceOf(User::class, $event->organizer);
        $this->assertEquals($organizer->id, $event->organizer->id);
    }

    public function test_event_has_ticket_types_relationship()
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id
        ]);

        $this->assertTrue($event->ticketTypes->contains($ticketType));
        $this->assertInstanceOf(TicketType::class, $event->ticketTypes->first());
    }

    public function test_event_has_bookings_relationship()
    {
        $event = Event::factory()->create();
        $booking = Booking::factory()->create([
            'event_id' => $event->id
        ]);

        $this->assertTrue($event->bookings->contains($booking));
        $this->assertInstanceOf(Booking::class, $event->bookings->first());
    }

    public function test_event_can_calculate_total_tickets_sold()
    {
        $event = Event::factory()->create();
        $ticketType1 = TicketType::factory()->create([
            'event_id' => $event->id,
            'quantity' => 100
        ]);
        $ticketType2 = TicketType::factory()->create([
            'event_id' => $event->id,
            'quantity' => 50
        ]);

        // Create confirmed bookings
        Booking::factory()->count(2)->create([
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType1->id,
            'quantity' => 5,
            'status' => 'confirmed'
        ]);

        Booking::factory()->create([
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType2->id,
            'quantity' => 3,
            'status' => 'confirmed'
        ]);

        // Create a pending booking (shouldn't be counted)
        Booking::factory()->create([
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType1->id,
            'quantity' => 2,
            'status' => 'pending'
        ]);

        $this->assertEquals(13, $event->getTotalTicketsSold());
    }

    public function test_event_can_check_if_sold_out()
    {
        $event = Event::factory()->create();

        // First test with a sold out event (no active tickets with remaining > 0)
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'status' => 'sold_out', // Use sold_out directly since active with 0 tickets auto-transitions
            'quantity' => 10,
            'tickets_remaining' => 0
        ]);

        $this->assertTrue($event->isSoldOut());

        // Create another ticket type with available tickets
        TicketType::factory()->create([
            'event_id' => $event->id,
            'status' => 'active',
            'quantity' => 10,
            'tickets_remaining' => 5
        ]);

        $this->assertFalse($event->fresh()->isSoldOut());
    }

    public function test_event_scope_upcoming()
    {
        // Create past event
        Event::factory()->create([
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->subHours(2)
        ]);

        // Create future event
        $upcomingEvent = Event::factory()->create([
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(2)
        ]);

        $upcomingEvents = Event::upcoming()->get();

        $this->assertTrue($upcomingEvents->contains($upcomingEvent));
        $this->assertEquals(1, $upcomingEvents->count());
    }
}
