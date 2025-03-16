<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Role;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCancellationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Event $event;
    private TicketType $ticketType;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $userRole = Role::factory()->create(['name' => 'user']);

        // Create user with proper role
        $this->user = User::factory()->create();
        $this->user->roles()->attach($userRole->id);

        $this->event = Event::factory()->create([
            'start_time' => Carbon::now()->addDays(5),
            'end_time' => Carbon::now()->addDays(5)->addHours(3),
            'status' => 'published'
        ]);

        $this->ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 1000,
            'quantity' => 100,
            'tickets_remaining' => 98,
            'status' => 'active'
        ]);

        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'quantity' => 2,
            'total_price' => 2000,
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);
    }

    public function test_user_can_cancel_their_own_booking()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/cancel");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'booking' => [
                    'id',
                    'status',
                    'cancelled_at'
                ]
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_cannot_cancel_already_cancelled_booking()
    {
        $this->booking->update(['status' => 'cancelled']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Booking is already cancelled'
            ]);
    }

    public function test_cannot_cancel_checked_in_booking()
    {
        $this->booking->update(['checked_in_at' => now()]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot cancel a booking that has been checked in'
            ]);
    }

    public function test_cannot_cancel_booking_after_event_starts()
    {
        $this->event->update([
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHours(2)
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cannot cancel booking after event has started'
            ]);
    }

    public function test_other_users_cannot_cancel_booking()
    {
        $otherUser = User::factory()->create();
        $otherUser->roles()->attach(Role::where('name', 'user')->first()->id);

        $response = $this->actingAs($otherUser)
            ->postJson("/api/v1/bookings/{$this->booking->id}/cancel");

        $response->assertForbidden();
    }

    public function test_cancelled_booking_updates_ticket_availability()
    {
        $originalTicketsRemaining = $this->ticketType->tickets_remaining;

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/cancel");

        $response->assertOk();

        $this->assertEquals(
            $originalTicketsRemaining + $this->booking->quantity,
            $this->ticketType->fresh()->tickets_remaining
        );
    }
}
