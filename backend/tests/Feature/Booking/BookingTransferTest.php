<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTransferTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $recipient;
    private Event $event;
    private TicketType $ticketType;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->recipient = User::factory()->create();

        $this->event = Event::factory()->create([
            'start_time' => Carbon::now()->addDays(5),
            'end_time' => Carbon::now()->addDays(5)->addHours(3),
            'status' => 'published'
        ]);

        $this->ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'active'
        ]);

        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'status' => 'confirmed',
            'payment_status' => 'paid'
        ]);
    }

    public function test_user_can_initiate_ticket_transfer()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/transfer/initiate");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'transfer_code',
                'expires_at'
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'transfer_status' => 'pending',
            'transfer_initiated_at' => now()->toDateTimeString()
        ]);

        $this->assertNotNull($this->booking->fresh()->transfer_code);
    }

    public function test_recipient_can_accept_transfer()
    {
        // First initiate the transfer
        $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/transfer/initiate");

        $transferCode = $this->booking->fresh()->transfer_code;

        // Now accept the transfer as recipient
        $response = $this->actingAs($this->recipient)
            ->postJson("/api/v1/bookings/transfer/accept", [
                'transfer_code' => $transferCode
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'booking' => [
                    'id',
                    'user_id',
                    'event',
                    'ticket_type'
                ]
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'user_id' => $this->recipient->id,
            'transfer_status' => 'completed',
            'transfer_code' => null
        ]);
    }

    public function test_cannot_transfer_non_confirmed_booking()
    {
        $this->booking->update(['status' => 'pending']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/transfer/initiate");

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => 'Only confirmed bookings can be transferred'
            ]);
    }

    public function test_cannot_transfer_checked_in_booking()
    {
        $this->booking->update(['checked_in_at' => now()]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/transfer/initiate");

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => 'Cannot transfer a checked-in booking'
            ]);
    }

    public function test_transfer_code_expires()
    {
        // Initiate transfer
        $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/transfer/initiate");

        $transferCode = $this->booking->fresh()->transfer_code;

        // Ensure transfer_expires_at is set
        $this->assertNotNull($this->booking->fresh()->transfer_expires_at);

        // Move time forward 25 hours
        $this->travel(25)->hours();

        // Attempt to accept expired transfer
        $response = $this->actingAs($this->recipient)
            ->postJson("/api/v1/bookings/transfer/accept", [
                'transfer_code' => $transferCode
            ]);

        $response->assertNotFound();
    }

    public function test_user_can_cancel_pending_transfer()
    {
        // Initiate transfer
        $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/transfer/initiate");

        // Cancel transfer
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/bookings/{$this->booking->id}/transfer");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Transfer cancelled successfully'
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'transfer_status' => null,
            'transfer_code' => null,
            'transfer_initiated_at' => null
        ]);
    }

    public function test_cannot_initiate_new_transfer_while_pending()
    {
        // Initiate first transfer
        $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/transfer/initiate");

        // Attempt to initiate second transfer
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/bookings/{$this->booking->id}/transfer/initiate");

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => 'A transfer is already pending for this booking'
            ]);
    }
}
