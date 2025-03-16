<?php

namespace Tests\Integration;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Common helper methods for integration tests
 */
trait TestHelpers
{
    /**
     * Create a user with the specified role
     *
     * @param string $role Role name ('Organizer', 'User', 'Admin')
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        $roleModel = Role::firstOrCreate(['name' => $role]);

        $defaultAttributes = [
            'email' => $role . '_' . Str::random(5) . '@example.com',
            'password' => Hash::make('password'),
        ];

        $user = User::factory()->create(array_merge($defaultAttributes, $attributes));
        $user->roles()->attach($roleModel);

        return $user;
    }

    /**
     * Create an event with the specified organizer
     *
     * @param User $organizer The event organizer
     * @param array $attributes Additional event attributes
     * @return Event
     */
    protected function createEvent(User $organizer, array $attributes = []): Event
    {
        $defaultAttributes = [
            'organizer_id' => $organizer->id,
            'title' => 'Test Event ' . Str::random(5),
            'description' => 'This is a test event description',
            'start_time' => now()->addDays(30),
            'end_time' => now()->addDays(30)->addHours(3),
            'location' => 'Test Location',
            'venue' => 'Test Venue',
            'category' => 'Conference',
            'status' => 'published',
        ];

        return Event::factory()->create(array_merge($defaultAttributes, $attributes));
    }

    /**
     * Create a ticket type for an event
     *
     * @param Event $event The event
     * @param array $attributes Additional ticket type attributes
     * @return TicketType
     */
    protected function createTicketType(Event $event, array $attributes = []): TicketType
    {
        $defaultAttributes = [
            'event_id' => $event->id,
            'name' => 'Regular Ticket',
            'description' => 'Standard admission',
            'price' => 1000,
            'quantity' => 100,
            'tickets_remaining' => 100,
            'status' => 'active',
        ];

        return TicketType::factory()->create(array_merge($defaultAttributes, $attributes));
    }

    /**
     * Create a complete booking with payment and ticket
     *
     * @param User $user The booking user
     * @param Event $event The event
     * @param TicketType $ticketType The ticket type
     * @param array $bookingAttributes Additional booking attributes
     * @param array $paymentAttributes Additional payment attributes
     * @return array Array containing booking, payment, and ticket
     */
    protected function createCompleteBooking(
        User $user,
        Event $event,
        TicketType $ticketType,
        array $bookingAttributes = [],
        array $paymentAttributes = []
    ): array {
        // Create booking
        $defaultBookingAttributes = [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
            'total_amount' => $ticketType->price,
            'booking_reference' => 'BK-' . Str::random(8),
        ];

        $booking = Booking::factory()->create(array_merge($defaultBookingAttributes, $bookingAttributes));

        // Create payment
        $defaultPaymentAttributes = [
            'booking_id' => $booking->id,
            'amount' => $ticketType->price,
            'status' => 'completed',
            'payment_method' => 'stripe',
            'transaction_reference' => 'TX-' . Str::random(10),
        ];

        $payment = Payment::factory()->create(array_merge($defaultPaymentAttributes, $paymentAttributes));

        // Create ticket
        $ticket = Ticket::factory()->create([
            'booking_id' => $booking->id,
            'ticket_type_id' => $ticketType->id,
            'status' => 'issued',
            'qr_code' => 'QR-' . Str::random(10),
            'check_in_status' => 'not_checked_in',
        ]);

        // Update ticket type count
        $ticketType->decrement('tickets_remaining');

        return [
            'booking' => $booking,
            'payment' => $payment,
            'ticket' => $ticket
        ];
    }

    /**
     * Get authentication token for a user
     *
     * @param User $user The user to authenticate
     * @return string The authentication token
     */
    protected function getAuthToken(User $user): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return $response->json('access_token');
    }

    /**
     * Mock Stripe payment processing
     *
     * @param string $paymentIntentId Custom payment intent ID (optional)
     * @return void
     */
    protected function mockStripePayment(?string $paymentIntentId = null): void
    {
        $paymentIntentId = $paymentIntentId ?? 'pi_' . Str::random(24);

        // Mock the Stripe service
        $this->mock('App\Services\StripeService')
            ->shouldReceive('createPaymentIntent')
            ->andReturn([
                'id' => $paymentIntentId,
                'client_secret' => 'cs_test_' . Str::random(24),
                'status' => 'requires_payment_method',
            ])
            ->shouldReceive('confirmPaymentIntent')
            ->andReturn([
                'id' => $paymentIntentId,
                'status' => 'succeeded',
            ]);
    }

    /**
     * Mock M-Pesa payment processing
     *
     * @param string $transactionId Custom transaction ID (optional)
     * @return void
     */
    protected function mockMpesaPayment(?string $transactionId = null): void
    {
        $transactionId = $transactionId ?? 'MPESA' . Str::random(10);

        // Mock the M-Pesa service
        $this->mock('App\Services\MpesaService')
            ->shouldReceive('initiatePayment')
            ->andReturn([
                'CheckoutRequestID' => 'ws_CO_' . Str::random(10),
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
            ])
            ->shouldReceive('confirmPayment')
            ->andReturn([
                'TransactionID' => $transactionId,
                'ResultCode' => '0',
                'ResultDesc' => 'The service request is processed successfully.',
            ]);
    }
}
