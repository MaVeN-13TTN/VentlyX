<?php

namespace Tests\Feature\CheckIn;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Role;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkCheckInTest extends TestCase
{
    use RefreshDatabase;

    private User $organizer;
    private User $otherOrganizer;
    private Event $event;
    private Event $otherEvent;
    private TicketType $ticketType;
    private TicketType $otherTicketType;
    private array $bookings = [];
    private array $otherEventBookings = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $organizerRole = Role::factory()->create(['name' => 'Organizer']);
        $userRole = Role::factory()->create(['name' => 'User']);

        // Create organizers
        $this->organizer = User::factory()->create();
        $this->organizer->roles()->attach($organizerRole->id);

        $this->otherOrganizer = User::factory()->create();
        $this->otherOrganizer->roles()->attach($organizerRole->id);

        // Create events that are currently happening
        $this->event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHours(2),
            'status' => 'published'
        ]);

        $this->otherEvent = Event::factory()->create([
            'organizer_id' => $this->otherOrganizer->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHours(2),
            'status' => 'published'
        ]);

        // Create ticket types
        $this->ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'status' => 'active'
        ]);

        $this->otherTicketType = TicketType::factory()->create([
            'event_id' => $this->otherEvent->id,
            'status' => 'active'
        ]);

        // Create multiple bookings for the main event
        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create();
            $user->roles()->attach($userRole->id);

            $this->bookings[] = Booking::factory()->create([
                'user_id' => $user->id,
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketType->id,
                'status' => 'confirmed',
                'payment_status' => 'paid'
            ]);
        }

        // Create a few bookings for the other event
        for ($i = 0; $i < 3; $i++) {
            $user = User::factory()->create();
            $user->roles()->attach($userRole->id);

            $this->otherEventBookings[] = Booking::factory()->create([
                'user_id' => $user->id,
                'event_id' => $this->otherEvent->id,
                'ticket_type_id' => $this->otherTicketType->id,
                'status' => 'confirmed',
                'payment_status' => 'paid'
            ]);
        }

        // Set one booking as already checked in
        $this->bookings[0]->update([
            'checked_in_at' => now(),
            'checked_in_by' => $this->organizer->id
        ]);
    }

    public function test_bulk_check_in_multiple_bookings()
    {
        $bookingIds = [
            $this->bookings[1]->id,
            $this->bookings[2]->id,
            $this->bookings[3]->id,
            $this->bookings[4]->id,
            $this->bookings[5]->id,
        ];

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/bulk", [
                'booking_ids' => $bookingIds
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'success_count',
                'failed_count',
                'success',
                'failed'
            ]);

        // Verify the success count
        $this->assertEquals(5, $response->json('success_count'));
        $this->assertEquals(0, $response->json('failed_count'));

        // Verify all bookings are checked in
        foreach ($bookingIds as $id) {
            $this->assertDatabaseHas('bookings', [
                'id' => $id,
                'checked_in_by' => $this->organizer->id
            ]);
            $this->assertNotNull(Booking::find($id)->checked_in_at);
        }
    }

    public function test_bulk_check_in_with_already_checked_in_booking()
    {
        $bookingIds = [
            $this->bookings[0]->id, // Already checked in
            $this->bookings[1]->id,
            $this->bookings[2]->id,
        ];

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/bulk", [
                'booking_ids' => $bookingIds
            ]);

        $response->assertOk();

        // Verify the success count (should include the already checked-in booking)
        $this->assertEquals(3, $response->json('success_count'));
        $this->assertEquals(0, $response->json('failed_count'));

        // Verify all bookings are checked in
        foreach ($bookingIds as $id) {
            $this->assertDatabaseHas('bookings', [
                'id' => $id,
                'checked_in_by' => $this->organizer->id
            ]);
            $this->assertNotNull(Booking::find($id)->checked_in_at);
        }
    }

    public function test_bulk_check_in_with_mixed_event_bookings()
    {
        $bookingIds = [
            $this->bookings[1]->id,
            $this->bookings[2]->id,
            $this->otherEventBookings[0]->id, // From other event
            $this->otherEventBookings[1]->id, // From other event
        ];

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/bulk", [
                'booking_ids' => $bookingIds
            ]);

        $response->assertOk();

        // Verify the success and failure counts
        $this->assertEquals(2, $response->json('success_count'));
        $this->assertEquals(2, $response->json('failed_count'));

        // Verify only the organizer's event bookings are checked in
        $this->assertDatabaseHas('bookings', [
            'id' => $this->bookings[1]->id,
            'checked_in_by' => $this->organizer->id
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $this->bookings[2]->id,
            'checked_in_by' => $this->organizer->id
        ]);

        // Verify other event bookings are not checked in
        $this->assertDatabaseMissing('bookings', [
            'id' => $this->otherEventBookings[0]->id,
            'checked_in_by' => $this->organizer->id
        ]);
        $this->assertDatabaseMissing('bookings', [
            'id' => $this->otherEventBookings[1]->id,
            'checked_in_by' => $this->organizer->id
        ]);
    }

    public function test_bulk_check_in_with_non_confirmed_bookings()
    {
        // Change one booking to pending status
        $this->bookings[3]->update(['status' => 'pending']);

        $bookingIds = [
            $this->bookings[1]->id,
            $this->bookings[2]->id,
            $this->bookings[3]->id, // Pending status
        ];

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/bulk", [
                'booking_ids' => $bookingIds
            ]);

        $response->assertOk();

        // Verify the success and failure counts
        $this->assertEquals(2, $response->json('success_count'));
        $this->assertEquals(0, $response->json('failed_count')); // The pending booking is filtered out before processing

        // Verify only confirmed bookings are checked in
        $this->assertDatabaseHas('bookings', [
            'id' => $this->bookings[1]->id,
            'checked_in_by' => $this->organizer->id
        ]);
        $this->assertDatabaseHas('bookings', [
            'id' => $this->bookings[2]->id,
            'checked_in_by' => $this->organizer->id
        ]);

        // Verify pending booking is not checked in
        $this->assertDatabaseMissing('bookings', [
            'id' => $this->bookings[3]->id,
            'checked_in_by' => $this->organizer->id
        ]);
    }

    public function test_unauthorized_user_cannot_bulk_check_in()
    {
        $regularUser = User::factory()->create();
        $regularUser->roles()->attach(Role::where('name', 'User')->first()->id);

        $bookingIds = [
            $this->bookings[1]->id,
            $this->bookings[2]->id,
        ];

        $response = $this->actingAs($regularUser)
            ->postJson("/api/v1/check-in/bulk", [
                'booking_ids' => $bookingIds
            ]);

        $response->assertForbidden();

        // Verify no bookings were checked in
        foreach ($bookingIds as $id) {
            $this->assertDatabaseMissing('bookings', [
                'id' => $id,
                'checked_in_by' => $regularUser->id
            ]);
        }
    }

    public function test_other_organizer_cannot_bulk_check_in_for_different_event()
    {
        $bookingIds = [
            $this->bookings[1]->id,
            $this->bookings[2]->id,
        ];

        $response = $this->actingAs($this->otherOrganizer)
            ->postJson("/api/v1/check-in/bulk", [
                'booking_ids' => $bookingIds
            ]);

        $response->assertOk();

        // Verify the failure count
        $this->assertEquals(0, $response->json('success_count'));
        $this->assertEquals(2, $response->json('failed_count'));

        // Verify no bookings were checked in
        foreach ($bookingIds as $id) {
            $this->assertDatabaseMissing('bookings', [
                'id' => $id,
                'checked_in_by' => $this->otherOrganizer->id
            ]);
        }
    }

    public function test_bulk_check_in_with_invalid_booking_ids()
    {
        $bookingIds = [
            $this->bookings[1]->id,
            999999, // Invalid ID
        ];

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/bulk", [
                'booking_ids' => $bookingIds
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_ids.1']);
    }

    public function test_bulk_check_in_with_empty_booking_ids()
    {
        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/bulk", [
                'booking_ids' => []
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_ids']);
    }
}
