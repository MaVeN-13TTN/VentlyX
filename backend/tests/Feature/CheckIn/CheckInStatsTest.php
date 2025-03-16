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

class CheckInStatsTest extends TestCase
{
    use RefreshDatabase;

    private User $organizer;
    private Event $event;
    private array $ticketTypes = [];
    private array $bookings = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $organizerRole = Role::factory()->create(['name' => 'Organizer']);
        $userRole = Role::factory()->create(['name' => 'User']);

        // Create organizer
        $this->organizer = User::factory()->create();
        $this->organizer->roles()->attach($organizerRole->id);

        // Create an event that is currently happening
        $this->event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHours(2),
            'status' => 'published'
        ]);

        // Create multiple ticket types for the event
        $this->ticketTypes = [
            'VIP' => TicketType::factory()->create([
                'event_id' => $this->event->id,
                'name' => 'VIP',
                'status' => 'active'
            ]),
            'Regular' => TicketType::factory()->create([
                'event_id' => $this->event->id,
                'name' => 'Regular',
                'status' => 'active'
            ]),
            'Early Bird' => TicketType::factory()->create([
                'event_id' => $this->event->id,
                'name' => 'Early Bird',
                'status' => 'active'
            ])
        ];

        // Create bookings for each ticket type
        foreach ($this->ticketTypes as $name => $ticketType) {
            for ($i = 0; $i < 10; $i++) {
                $user = User::factory()->create();
                $user->roles()->attach($userRole->id);

                $this->bookings[] = Booking::factory()->create([
                    'user_id' => $user->id,
                    'event_id' => $this->event->id,
                    'ticket_type_id' => $ticketType->id,
                    'status' => 'confirmed',
                    'payment_status' => 'paid'
                ]);
            }
        }

        // Check in some bookings at different times
        // VIP tickets: 8 out of 10 checked in
        for ($i = 0; $i < 8; $i++) {
            $booking = Booking::where('ticket_type_id', $this->ticketTypes['VIP']->id)
                ->whereNull('checked_in_at')
                ->first();

            $booking->update([
                'checked_in_at' => Carbon::now()->subMinutes(rand(5, 55)),
                'checked_in_by' => $this->organizer->id
            ]);
        }

        // Regular tickets: 5 out of 10 checked in
        for ($i = 0; $i < 5; $i++) {
            $booking = Booking::where('ticket_type_id', $this->ticketTypes['Regular']->id)
                ->whereNull('checked_in_at')
                ->first();

            $booking->update([
                'checked_in_at' => Carbon::now()->subMinutes(rand(5, 55)),
                'checked_in_by' => $this->organizer->id
            ]);
        }

        // Early Bird tickets: 2 out of 10 checked in
        for ($i = 0; $i < 2; $i++) {
            $booking = Booking::where('ticket_type_id', $this->ticketTypes['Early Bird']->id)
                ->whereNull('checked_in_at')
                ->first();

            $booking->update([
                'checked_in_at' => Carbon::now()->subMinutes(rand(5, 55)),
                'checked_in_by' => $this->organizer->id
            ]);
        }
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

        // Check that the check-in rate calculation is correct
        $expectedRate = $totalAttendees > 0 ? round(($checkedIn / $totalAttendees) * 100, 2) : 0;
        $this->assertEquals($expectedRate, $checkInRate);
    }

    public function test_check_in_stats_by_ticket_type()
    {
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");

        $response->assertOk();

        $ticketTypeStats = $response->json('check_ins_by_ticket_type');
        $this->assertCount(3, $ticketTypeStats);

        // Find each ticket type in the response
        $vipStats = collect($ticketTypeStats)->firstWhere('name', 'VIP');
        $regularStats = collect($ticketTypeStats)->firstWhere('name', 'Regular');
        $earlyBirdStats = collect($ticketTypeStats)->firstWhere('name', 'Early Bird');

        // Verify the check-in counts for each ticket type
        $this->assertEquals(8, $vipStats['checked_in']);
        $this->assertEquals(10, $vipStats['total']);

        $this->assertEquals(5, $regularStats['checked_in']);
        $this->assertEquals(10, $regularStats['total']);

        $this->assertEquals(2, $earlyBirdStats['checked_in']);
        $this->assertEquals(10, $earlyBirdStats['total']);
    }

    public function test_check_in_stats_by_hour()
    {
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");

        $response->assertOk();

        $hourlyStats = $response->json('check_ins_by_hour');

        // Since we're using random times for check-ins, we just verify the structure
        $this->assertIsArray($hourlyStats);

        // Each entry should have hour and count
        if (count($hourlyStats) > 0) {
            $this->assertArrayHasKey('hour', $hourlyStats[0]);
            $this->assertArrayHasKey('count', $hourlyStats[0]);
        }
    }

    public function test_unauthorized_user_cannot_access_check_in_stats()
    {
        $regularUser = User::factory()->create();
        $regularUser->roles()->attach(Role::where('name', 'User')->first()->id);

        $response = $this->actingAs($regularUser)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");

        $response->assertForbidden();
    }

    public function test_check_in_stats_for_event_with_no_bookings()
    {
        // Create a new event with no bookings
        $emptyEvent = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => 'published'
        ]);

        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/check-in/events/{$emptyEvent->id}/stats");

        $response->assertOk()
            ->assertJson([
                'total_attendees' => 0,
                'checked_in' => 0,
                'check_in_rate' => 0,
                'check_ins_by_hour' => [],
                'check_ins_by_ticket_type' => []
            ]);
    }

    public function test_admin_can_access_check_in_stats_for_any_event()
    {
        // Create admin user
        $adminRole = Role::factory()->create(['name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $response = $this->actingAs($admin)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");

        $response->assertOk()
            ->assertJsonStructure([
                'total_attendees',
                'checked_in',
                'check_in_rate'
            ]);
    }

    public function test_check_in_rate_calculation()
    {
        // Check in more bookings to change the rate
        $booking = Booking::where('ticket_type_id', $this->ticketTypes['Regular']->id)
            ->whereNull('checked_in_at')
            ->first();

        $booking->update([
            'checked_in_at' => now(),
            'checked_in_by' => $this->organizer->id
        ]);

        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");

        $response->assertOk();

        // Get the actual values from the response
        $totalAttendees = $response->json('total_attendees');
        $checkedIn = $response->json('checked_in');
        $checkInRate = $response->json('check_in_rate');

        // Verify the stats are correct
        $this->assertIsNumeric($totalAttendees);
        $this->assertIsNumeric($checkedIn);
        $this->assertIsNumeric($checkInRate);

        // Check that the check-in rate calculation is correct
        $expectedRate = $totalAttendees > 0 ? round(($checkedIn / $totalAttendees) * 100, 2) : 0;
        $this->assertEquals($expectedRate, $checkInRate);
    }
}
