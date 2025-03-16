<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Event $event;
    private TicketType $ticketType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->event = Event::factory()->create([
            'start_time' => Carbon::now()->addDays(5),
            'end_time' => Carbon::now()->addDays(5)->addHours(3),
            'status' => 'published'
        ]);

        $this->ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 1000,
            'quantity' => 100,
            'tickets_remaining' => 100,
            'status' => 'active'
        ]);
    }

    public function test_user_can_create_booking()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/bookings', [
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'quantity' => 2
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'booking' => [
                    'id',
                    'user_id',
                    'event_id',
                    'ticket_type_id',
                    'quantity',
                    'total_price',
                    'status',
                    'payment_status'
                ]
            ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'quantity' => 2,
            'total_price' => 2000,
            'status' => 'pending',
            'payment_status' => 'pending'
        ]);

        // Verify tickets_remaining was decremented
        $this->assertEquals(98, $this->ticketType->fresh()->tickets_remaining);
    }

    public function test_cannot_book_more_tickets_than_available()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/bookings', [
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'quantity' => 101
        ]);

        $response->assertStatus(422);

        // Verify tickets_remaining wasn't changed
        $this->assertEquals(100, $this->ticketType->fresh()->tickets_remaining);
    }

    public function test_cannot_book_for_ended_event()
    {
        $this->event->update([
            'end_time' => Carbon::now()->subDay()
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/bookings', [
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'quantity' => 1
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_view_own_bookings()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/bookings');

        $response->assertOk()
            ->assertJsonStructure([
                'bookings' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'event_id',
                            'ticket_type_id',
                            'quantity',
                            'total_price',
                            'status',
                            'payment_status',
                            'event',
                            'ticketType'
                        ]
                    ]
                ]
            ]);
    }

    public function test_user_can_view_single_booking()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/bookings/{$booking->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'booking' => [
                    'id',
                    'user_id',
                    'event_id',
                    'ticket_type_id',
                    'quantity',
                    'total_price',
                    'status',
                    'payment_status',
                    'event',
                    'ticketType',
                    'payment'
                ]
            ]);
    }

    public function test_user_cannot_view_other_users_booking()
    {
        $otherUser = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $otherUser->id,
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/bookings/{$booking->id}");

        $response->assertNotFound();
    }
}
