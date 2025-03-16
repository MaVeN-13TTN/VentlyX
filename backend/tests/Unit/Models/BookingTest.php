<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Models\TicketType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_can_be_created()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'price' => 1000
        ]);

        $bookingData = [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
            'total_price' => 2000,
            'status' => 'pending',
            'payment_status' => 'pending'
        ];

        $booking = Booking::create($bookingData);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'quantity' => 2
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
    }

    public function test_booking_belongs_to_user()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $booking->user);
        $this->assertEquals($user->id, $booking->user->id);
    }

    public function test_booking_belongs_to_event()
    {
        $event = Event::factory()->create();
        $booking = Booking::factory()->create([
            'event_id' => $event->id
        ]);

        $this->assertInstanceOf(Event::class, $booking->event);
        $this->assertEquals($event->id, $booking->event->id);
    }

    public function test_booking_belongs_to_ticket_type()
    {
        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id
        ]);
        $booking = Booking::factory()->create([
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id
        ]);

        $this->assertInstanceOf(TicketType::class, $booking->ticketType);
        $this->assertEquals($ticketType->id, $booking->ticketType->id);
    }

    public function test_booking_can_generate_qr_code()
    {
        $booking = Booking::factory()->create();
        $booking->generateQrCode();

        $this->assertNotNull($booking->qr_code_url);
        // Check that the QR code URL follows the expected pattern
        $this->assertMatchesRegularExpression('/^qr_codes\/\d+_\d+\.png$/', $booking->qr_code_url);
    }

    public function test_booking_can_be_checked_in()
    {
        // Create an event with start and end times that include the current time
        $event = Event::factory()->create([
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour()
        ]);

        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'event_id' => $event->id
        ]);

        $this->assertNull($booking->checked_in_at);

        $booking->checkIn();

        $this->assertNotNull($booking->fresh()->checked_in_at);
    }

    public function test_booking_cannot_be_checked_in_if_not_confirmed()
    {
        $booking = Booking::factory()->create([
            'status' => 'pending',
            'payment_status' => 'pending'
        ]);

        $this->expectException(\Exception::class);
        $booking->checkIn();
    }

    public function test_booking_can_calculate_total_price()
    {
        $ticketType = TicketType::factory()->create([
            'price' => 1000
        ]);

        $booking = Booking::factory()->create([
            'ticket_type_id' => $ticketType->id,
            'quantity' => 3
        ]);

        $this->assertEquals(3000, $booking->calculateTotalPrice());
    }

    public function test_booking_can_be_cancelled()
    {
        $booking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);

        $booking->cancel();

        $this->assertEquals('cancelled', $booking->fresh()->status);
        $this->assertEquals('cancelled', $booking->fresh()->payment_status);
    }

    public function test_booking_scope_confirmed()
    {
        // Create various bookings with different statuses
        $confirmedBooking = Booking::factory()->create(['status' => 'confirmed']);
        Booking::factory()->create(['status' => 'pending']);
        Booking::factory()->create(['status' => 'cancelled']);

        $confirmedBookings = Booking::confirmed()->get();

        $this->assertEquals(1, $confirmedBookings->count());
        $this->assertTrue($confirmedBookings->contains($confirmedBooking));
    }

    public function test_can_make_new_booking_after_cancellation()
    {
        // Create and cancel first booking
        $firstBooking = Booking::factory()->create([
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);

        $firstBooking->cancel();

        // Attempt to create a new booking
        $newBooking = Booking::factory()->create([
            'status' => 'pending',
            'payment_status' => 'pending'
        ]);

        $this->assertEquals('pending', $newBooking->status);
        $this->assertNotEquals($firstBooking->id, $newBooking->id);
    }
}
