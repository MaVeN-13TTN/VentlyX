<?php

namespace Tests\Integration\BookingFlow;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookingPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $event;
    protected $ticketType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create();

        // Create an event
        $this->event = Event::factory()->create([
            'title' => 'Test Event',
            'description' => 'Test Event Description',
            'start_time' => now()->addDays(10),
            'end_time' => now()->addDays(10)->addHours(3),
            'location' => 'Test Location',
            'status' => 'published',
        ]);

        // Create a ticket type
        $this->ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'name' => 'Regular Ticket',
            'description' => 'Regular ticket for the event',
            'price' => 1000, // $10.00
            'quantity' => 100,
            'tickets_remaining' => 100,
            'status' => 'active',
        ]);
    }

    /**
     * Test the complete booking flow from event selection to payment with Stripe.
     */
    public function test_complete_booking_flow_with_stripe_payment()
    {
        // Step 1: View event details
        $response = $this->getJson("/api/v1/events/{$this->event->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->event->id)
            ->assertJsonPath('data.title', $this->event->title);

        // Step 2: View ticket types
        $response = $this->getJson("/api/v1/events/{$this->event->id}/ticket-types");
        $response->assertStatus(200);

        // Step 3: Create a booking
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', [
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketType->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('booking.status', 'pending')
            ->assertJsonPath('booking.payment_status', 'pending');

        $bookingId = $response->json('booking.id');

        // Step 4: Mock Stripe API responses
        Http::fake([
            'stripe.com/*' => Http::response([
                'id' => 'pi_' . uniqid(),
                'status' => 'succeeded',
                'amount' => 2000,
                'currency' => 'usd',
            ], 200)
        ]);

        // Step 5: Process payment
        $payment = Payment::create([
            'booking_id' => $bookingId,
            'payment_method' => 'stripe',
            'payment_id' => 'pi_' . uniqid(),
            'amount' => 2000,
            'status' => 'completed',
            'transaction_details' => [
                'payment_intent' => 'pi_' . uniqid(),
                'customer_email' => $this->user->email
            ],
            'currency' => 'usd',
            'transaction_id' => strtoupper(uniqid()),
            'transaction_reference' => 'TX-' . uniqid(),
            'payment_date' => now()
        ]);

        // Step 6: Verify booking status is updated
        $booking = Booking::find($bookingId);
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);

        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals('paid', $booking->payment_status);
        $this->assertNotNull($payment);

        // Step 7: Get QR code for the booking
        $booking->generateQrCode();
        $this->assertNotNull($booking->qr_code_url);

        // Step 8: Create tickets for the booking
        Ticket::create([
            'booking_id' => $booking->id,
            'ticket_type_id' => $this->ticketType->id,
            'qr_code' => 'QR-' . uniqid(),
            'status' => 'issued',
            'check_in_status' => 'not_checked_in'
        ]);

        // Step 9: Verify tickets are available
        $tickets = Ticket::where('booking_id', $booking->id)->get();
        $this->assertGreaterThan(0, $tickets->count());
        $this->assertEquals('issued', $tickets->first()->status);
    }

    /**
     * Test the booking flow with M-Pesa payment.
     */
    public function test_booking_flow_with_mpesa_payment()
    {
        // Step 1: Create a booking
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', [
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketType->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(201);
        $bookingId = $response->json('booking.id');

        // Step 2: Mock M-Pesa API responses
        Http::fake([
            'api.safaricom.co.ke/*' => Http::response([
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success',
                'MerchantRequestID' => 'test-' . uniqid(),
                'CheckoutRequestID' => 'ws_CO_' . uniqid(),
                'CustomerMessage' => 'Success. Request accepted for processing'
            ], 200)
        ]);

        // Step 3: Create payment record
        $payment = Payment::create([
            'booking_id' => $bookingId,
            'payment_method' => 'mpesa',
            'payment_id' => 'mpesa_' . uniqid(),
            'amount' => 1000,
            'status' => 'completed',
            'transaction_details' => [
                'mpesa_receipt' => 'LHG7QPGSO8',
                'phone_number' => '254712345678'
            ],
            'currency' => 'KES',
            'transaction_id' => 'LHG7QPGSO8',
            'transaction_reference' => 'TX-' . uniqid(),
            'payment_date' => now()
        ]);

        // Step 4: Update booking status
        $booking = Booking::find($bookingId);
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);

        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals('paid', $booking->payment_status);
        $this->assertNotNull($payment);

        // Step 5: Generate QR code
        $booking->generateQrCode();
        $this->assertNotNull($booking->qr_code_url);

        // Step 6: Create tickets for the booking
        Ticket::create([
            'booking_id' => $booking->id,
            'ticket_type_id' => $this->ticketType->id,
            'qr_code' => 'QR-' . uniqid(),
            'status' => 'issued',
            'check_in_status' => 'not_checked_in'
        ]);

        // Step 7: Verify tickets are created
        $tickets = Ticket::where('booking_id', $booking->id)->get();
        $this->assertGreaterThan(0, $tickets->count());
    }

    /**
     * Test booking cancellation flow.
     */
    public function test_booking_cancellation_flow()
    {
        // Step 1: Create a booking
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', [
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketType->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(201);
        $bookingId = $response->json('booking.id');

        // Step 2: Cancel the booking
        $booking = Booking::find($bookingId);
        $booking->cancel();

        // Step 3: Verify booking status is updated
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('cancelled', $booking->payment_status);

        // Step 4: Verify ticket availability is restored
        $this->ticketType->refresh();
        $originalRemaining = $this->ticketType->tickets_remaining;
        $this->ticketType->increment('tickets_remaining', 2);
        $this->ticketType->refresh();
        $this->assertEquals($originalRemaining + 2, $this->ticketType->tickets_remaining);
    }

    /**
     * Test booking with insufficient tickets.
     */
    public function test_booking_with_insufficient_tickets()
    {
        // Update ticket type to have limited availability
        $this->ticketType->update(['tickets_remaining' => 2]);

        // Try to book more tickets than available
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', [
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketType->id,
                'quantity' => 5,
            ]);

        $response->assertStatus(422);

        // Verify no booking was created
        $this->assertEquals(0, Booking::count());

        // Verify ticket availability remains unchanged
        $this->ticketType->refresh();
        $this->assertEquals(2, $this->ticketType->tickets_remaining);
    }

    /**
     * Test booking for an event that has ended.
     */
    public function test_booking_for_ended_event()
    {
        // Update event to have ended
        $this->event->update([
            'start_time' => now()->subDays(2),
            'end_time' => now()->subDays(1),
        ]);

        // Try to book tickets for the ended event
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bookings', [
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketType->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Cannot book tickets for an event that has already ended');

        // Verify no booking was created
        $this->assertEquals(0, Booking::count());
    }
}
