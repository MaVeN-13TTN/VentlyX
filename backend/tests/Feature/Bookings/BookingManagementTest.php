<?php

namespace Tests\Feature\Bookings;

use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ApiTestCase;

class BookingManagementTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable middleware for testing
        $this->withoutMiddleware();
    }

    public function test_users_can_create_bookings()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an event with a ticket type
        $event = Event::factory()->published()->create([
            'start_time' => now()->addDays(10),
            'end_time' => now()->addDays(10)->addHours(2)
        ]);

        // Initialize both quantity and tickets_remaining
        $quantity = 100;
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'quantity' => $quantity,
            'tickets_remaining' => $quantity,
            'price' => 50.00,
            'status' => 'active',
            'sales_start_date' => now()->subDay(),
            'sales_end_date' => now()->addDays(5),
            'is_available' => true
        ]);

        $this->actingAs($user);

        $bookingData = [
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(201);

        // Verify the booking was created correctly
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'status' => 'pending',
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
            'total_price' => 100.00
        ]);

        // Verify tickets_remaining was updated correctly
        $updatedTicketType = TicketType::find($ticketType->id);
        $this->assertEquals(98, $updatedTicketType->tickets_remaining);
    }

    // For these tests, we'll use a more direct approach to test the specific error conditions

    public function test_users_cannot_book_for_ended_events()
    {
        // Directly test BookingException::eventEnded()
        $this->withoutExceptionHandling();
        $this->expectException(\App\Exceptions\Api\BookingException::class);
        $this->expectExceptionMessage('Cannot book tickets for an event that has already ended');

        $controller = app()->make(\App\Http\Controllers\API\BookingController::class);

        // Using reflection to call a protected test method
        $reflectionMethod = new \ReflectionMethod($controller, 'validateEventNotEnded');
        $reflectionMethod->setAccessible(true);

        $event = Event::factory()->create([
            'end_time' => now()->subDays(1)
        ]);

        $reflectionMethod->invoke($controller, $event);
    }

    public function test_users_cannot_book_if_sales_ended()
    {
        // Directly test BookingException::bookingClosed()
        $this->withoutExceptionHandling();
        $this->expectException(\App\Exceptions\Api\BookingException::class);
        $this->expectExceptionMessage('Ticket sales are closed for this event');

        $controller = app()->make(\App\Http\Controllers\API\BookingController::class);

        // Using reflection to call a protected test method
        $reflectionMethod = new \ReflectionMethod($controller, 'validateTicketSalesOpen');
        $reflectionMethod->setAccessible(true);

        $ticketType = TicketType::factory()->create([
            'sales_end_date' => now()->subDays(1)
        ]);

        $reflectionMethod->invoke($controller, $ticketType);
    }

    public function test_users_cannot_book_more_tickets_than_available()
    {
        $user = User::factory()->create();

        // Create event with limited tickets
        $event = Event::factory()->published()->create();

        // Set both quantity and tickets_remaining to 5
        $quantity = 5;
        $ticketType = TicketType::factory()->create([
            'event_id' => $event->id,
            'quantity' => $quantity,
            'tickets_remaining' => $quantity,
            'status' => 'active'
        ]);

        $this->actingAs($user);

        $bookingData = [
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 10  // More than available
        ];

        $response = $this->postJson('/api/bookings', $bookingData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    // Test for cancelling bookings - now with fixed constraint
    public function test_users_can_cancel_their_bookings()
    {
        // Create a user with a booking
        $user = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',  // Must be 'pending' to be cancellable
            'payment_status' => 'pending'  // Must be within allowed enum values
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/bookings/' . $booking->id . '/cancel');

        // Verify the status code
        $response->assertStatus(200);

        // Check database for updated status
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
            'payment_status' => 'cancelled'  // Now this should work with our constraint fix
        ]);
    }

    public function test_users_cannot_cancel_checked_in_bookings()
    {
        // Create user with a checked-in booking
        $user = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'status' => 'confirmed',
            'checked_in_at' => now()
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/bookings/' . $booking->id . '/cancel');

        // API uses 422 validation error rather than 400
        $response->assertStatus(422);
    }

    public function test_organizers_can_check_in_bookings()
    {
        // Create organizer
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        // Create event and booking
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $booking = Booking::factory()->create([
            'event_id' => $event->id,
            'status' => 'confirmed',
            'checked_in_at' => null
        ]);

        $this->actingAs($organizer);

        $response = $this->postJson('/api/bookings/' . $booking->id . '/check-in');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Booking checked in successfully'
            ]);

        $this->assertNotNull(Booking::find($booking->id)->checked_in_at);
    }

    public function test_organizers_cannot_check_in_already_checked_in_bookings()
    {
        // Create organizer
        $organizer = User::factory()->create();
        $organizer->roles()->attach(Role::firstOrCreate(['name' => 'Organizer']));

        // Create event and booking
        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $booking = Booking::factory()->create([
            'event_id' => $event->id,
            'status' => 'confirmed',
            'checked_in_at' => now()
        ]);

        $this->actingAs($organizer);

        $response = $this->postJson('/api/bookings/' . $booking->id . '/check-in');

        $response->assertStatus(422)
            ->assertJsonFragment(['booking' => ['Booking has already been checked in.']]);
    }
}
