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

class CheckInControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $organizer;
    private User $admin;
    private Event $event;
    private TicketType $ticketType;
    private array $bookings = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles first
        $organizerRole = Role::factory()->create(['name' => 'Organizer']);
        $userRole = Role::factory()->create(['name' => 'User']);
        $adminRole = Role::factory()->create(['name' => 'Admin']);

        // Create users with proper role assignments
        $this->organizer = User::factory()->create();
        $this->organizer->roles()->attach($organizerRole->id);

        $this->user = User::factory()->create();
        $this->user->roles()->attach($userRole->id);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole->id);

        // Create an event that is currently happening
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

        // Create multiple bookings for testing
        for ($i = 0; $i < 5; $i++) {
            $this->bookings[] = Booking::factory()->create([
                'user_id' => User::factory()->create()->id,
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketType->id,
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

    public function test_get_attendees_list()
    {
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/attendees");

        $response->assertOk()
            ->assertJsonStructure([
                'attendees',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);

        // Verify we have the correct number of attendees
        $this->assertEquals(5, $response->json('meta.total'));
    }

    public function test_get_attendees_with_search_filter()
    {
        // Update a booking's user with a specific name for searching
        $searchUser = User::find($this->bookings[1]->user_id);
        $searchUser->update(['name' => 'SearchableUserName']);

        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/attendees?search=Searchable");

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('SearchableUserName', $response->json('attendees.0.user.name'));
    }

    public function test_get_attendees_with_check_in_filter()
    {
        // Filter for checked-in attendees
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/attendees?checked_in=true");

        $response->assertOk();
        $this->assertEquals(1, $response->json('meta.total'));

        // Filter for not checked-in attendees
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/attendees?checked_in=false");

        $response->assertOk();
        $this->assertEquals(4, $response->json('meta.total'));
    }

    public function test_verify_qr_code()
    {
        // Generate QR code data
        $qrData = json_encode([
            'booking_id' => $this->bookings[1]->id,
            'event_id' => $this->event->id,
            'user_id' => $this->bookings[1]->user_id,
            'timestamp' => now()->timestamp
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/verify", [
                'qr_data' => $qrData,
                'event_id' => $this->event->id
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'valid',
                'booking',
                'checked_in'
            ])
            ->assertJson([
                'valid' => true,
                'checked_in' => false
            ]);
    }

    public function test_verify_already_checked_in_qr_code()
    {
        // Generate QR code data for already checked-in booking
        $qrData = json_encode([
            'booking_id' => $this->bookings[0]->id,
            'event_id' => $this->event->id,
            'user_id' => $this->bookings[0]->user_id,
            'timestamp' => now()->timestamp
        ]);

        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/verify", [
                'qr_data' => $qrData,
                'event_id' => $this->event->id
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'valid',
                'booking',
                'checked_in',
                'checked_in_at'
            ])
            ->assertJson([
                'valid' => true,
                'checked_in' => true
            ]);
    }

    public function test_undo_check_in()
    {
        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/bookings/{$this->bookings[0]->id}/undo");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'booking'
            ]);

        // Verify the booking is no longer checked in
        $this->assertDatabaseHas('bookings', [
            'id' => $this->bookings[0]->id,
            'checked_in_at' => null,
            'checked_in_by' => null
        ]);
    }

    public function test_cannot_undo_check_in_for_not_checked_in_booking()
    {
        $response = $this->actingAs($this->organizer)
            ->postJson("/api/v1/check-in/bookings/{$this->bookings[1]->id}/undo");

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => 'Attendee has not been checked in'
            ]);
    }

    public function test_get_check_in_stats()
    {
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");

        $response->assertOk()
            ->assertJsonStructure([
                'total_attendees',
                'checked_in',
                'check_in_rate',
                'check_ins_by_hour',
                'check_ins_by_ticket_type'
            ]);

        // Get the actual values from the response
        $totalAttendees = $response->json('total_attendees');
        $checkedIn = $response->json('checked_in');
        $checkInRate = $response->json('check_in_rate');

        // Verify the stats are correct
        $this->assertIsNumeric($totalAttendees);
        $this->assertIsNumeric($checkedIn);
        $this->assertIsNumeric($checkInRate);
        $this->assertEquals($checkInRate, round(($checkedIn / $totalAttendees) * 100, 2));
    }

    public function test_bulk_check_in()
    {
        $bookingIds = [
            $this->bookings[1]->id,
            $this->bookings[2]->id,
            $this->bookings[3]->id
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
        $this->assertEquals(3, $response->json('success_count'));
        $this->assertEquals(0, $response->json('failed_count'));

        // Verify the bookings are checked in
        foreach ($bookingIds as $id) {
            $this->assertDatabaseHas('bookings', [
                'id' => $id,
                'checked_in_by' => $this->organizer->id
            ]);
            $this->assertNotNull(Booking::find($id)->checked_in_at);
        }
    }

    public function test_unauthorized_user_cannot_access_check_in_features()
    {
        $regularUser = User::factory()->create();
        $regularUser->roles()->attach(Role::where('name', 'User')->first()->id);

        // Try to get attendees
        $response = $this->actingAs($regularUser)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/attendees");
        $response->assertForbidden();

        // Try to check in
        $response = $this->actingAs($regularUser)
            ->postJson("/api/v1/check-in/bookings/{$this->bookings[1]->id}");
        $response->assertForbidden();

        // Try to get stats
        $response = $this->actingAs($regularUser)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");
        $response->assertForbidden();
    }

    public function test_admin_can_access_all_check_in_features()
    {
        // Admin can get attendees
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/attendees");
        $response->assertOk();

        // Admin can check in
        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/check-in/bookings/{$this->bookings[1]->id}");
        $response->assertOk();

        // Admin can get stats
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");
        $response->assertOk();
    }
}
