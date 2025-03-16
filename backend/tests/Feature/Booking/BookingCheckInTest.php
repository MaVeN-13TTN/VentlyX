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

class BookingCheckInTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $organizer;
    private Event $event;
    private TicketType $ticketType;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles first
        $organizerRole = Role::factory()->create(['name' => 'organizer']);
        $userRole = Role::factory()->create(['name' => 'user']);

        // Create users with proper role assignments
        $this->organizer = User::factory()->create();
        $this->organizer->roles()->attach($organizerRole->id);

        $this->user = User::factory()->create();
        $this->user->roles()->attach($userRole->id);

        $this->event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHours(2),
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

    public function test_organizer_can_check_in_confirmed_booking()
    {
        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/bookings/{$this->booking->id}/check-in");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'booking' => [
                    'id',
                    'checked_in_at'
                ]
            ]);

        // Verify the booking was checked in without relying on exact timestamp
        $booking = $this->booking->fresh();
        $this->assertNotNull($booking->checked_in_at);
        $this->assertEquals('confirmed', $booking->status);
    }

    public function test_cannot_check_in_unconfirmed_booking()
    {
        $this->booking->update(['status' => 'pending']);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/bookings/{$this->booking->id}/check-in");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Invalid booking status: pending. Expected one of: [confirmed]'
            ]);
    }

    public function test_cannot_check_in_before_event_starts()
    {
        $this->event->update([
            'start_time' => Carbon::now()->addHour(),
            'end_time' => Carbon::now()->addHours(3)
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/bookings/{$this->booking->id}/check-in");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Check-in is only available during the event'
            ]);
    }

    public function test_cannot_check_in_after_event_ends()
    {
        $this->event->update([
            'start_time' => Carbon::now()->subHours(3),
            'end_time' => Carbon::now()->subHour()
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/bookings/{$this->booking->id}/check-in");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Check-in is only available during the event'
            ]);
    }

    public function test_cannot_check_in_already_checked_in_booking()
    {
        $this->booking->update(['checked_in_at' => now()]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/bookings/{$this->booking->id}/check-in");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Booking has already been checked in'
            ]);
    }

    public function test_non_organizer_cannot_check_in_booking()
    {
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser)
            ->postJson("/api/v1/bookings/{$this->booking->id}/check-in");

        $response->assertForbidden();
    }

    public function test_can_verify_booking_check_in_status()
    {
        $checkedInTime = now();
        $this->booking->update(['checked_in_at' => $checkedInTime]);

        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/bookings/{$this->booking->id}");

        $response->assertOk();

        // Get the actual checked_in_at value from the response
        $responseData = json_decode($response->getContent(), true);
        $this->assertNotNull($responseData['booking']['checked_in_at']);

        // Verify the booking has been checked in
        $this->assertDatabaseHas('bookings', [
            'id' => $this->booking->id,
            'checked_in_at' => $checkedInTime->toDateTimeString()
        ]);
    }
}
