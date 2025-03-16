<?php

namespace Tests\Feature\Analytics;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Role;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $organizer;
    private User $user;
    private Event $event;
    private array $ticketTypes;
    private array $bookings;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $organizerRole = Role::firstOrCreate(['name' => 'Organizer']);
        $userRole = Role::firstOrCreate(['name' => 'User']);

        // Create users with different roles
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        $this->organizer = User::factory()->create();
        $this->organizer->roles()->attach($organizerRole);

        $this->user = User::factory()->create();
        $this->user->roles()->attach($userRole);

        // Create an event
        $this->event = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'status' => 'published',
            'start_time' => Carbon::now()->subHours(5),
            'end_time' => Carbon::now()->addHours(3),
        ]);

        // Create ticket types
        $this->ticketTypes = [
            TicketType::factory()->create([
                'event_id' => $this->event->id,
                'name' => 'VIP',
                'price' => 5000,
            ]),
            TicketType::factory()->create([
                'event_id' => $this->event->id,
                'name' => 'Regular',
                'price' => 2000,
            ]),
        ];

        // Create bookings with different check-in times
        $this->createBookingsWithCheckIns();
    }

    private function createBookingsWithCheckIns(): void
    {
        $this->bookings = [];

        // Create 20 confirmed bookings
        for ($i = 0; $i < 20; $i++) {
            $ticketTypeId = $i < 5 ? $this->ticketTypes[0]->id : $this->ticketTypes[1]->id;
            $price = $i < 5 ? 5000 : 2000;

            $booking = Booking::factory()->create([
                'event_id' => $this->event->id,
                'ticket_type_id' => $ticketTypeId,
                'user_id' => User::factory()->create()->id,
                'quantity' => 1,
                'total_price' => $price,
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ]);

            // Check in 15 out of 20 bookings at different times
            if ($i < 15) {
                // Distribute check-ins across different hours
                $hourOffset = $i % 5;
                $booking->update([
                    'checked_in_at' => Carbon::now()->subHours(4)->addHours($hourOffset),
                    'checked_in_by' => $this->organizer->id,
                ]);
            }

            $this->bookings[] = $booking;
        }
    }

    public function test_organizer_can_get_check_in_stats()
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

        // Verify the stats match our test data
        $this->assertEquals(20, $response->json('total_attendees'));
        $this->assertEquals(15, $response->json('checked_in'));
        $this->assertEquals(75, $response->json('check_in_rate'));

        // Verify check-ins by ticket type
        $checkInsByTicketType = $response->json('check_ins_by_ticket_type');
        $this->assertCount(2, $checkInsByTicketType);

        // Find VIP ticket type stats
        $vipStats = collect($checkInsByTicketType)->firstWhere('name', 'VIP');
        $this->assertNotNull($vipStats);

        // Find Regular ticket type stats
        $regularStats = collect($checkInsByTicketType)->firstWhere('name', 'Regular');
        $this->assertNotNull($regularStats);

        // Check hourly distribution
        $hourlyDistribution = $response->json('check_ins_by_hour');
        $this->assertIsArray($hourlyDistribution);
    }

    public function test_admin_can_get_check_in_stats()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");

        $response->assertOk()
            ->assertJsonStructure([
                'total_attendees',
                'checked_in',
                'check_in_rate',
                'check_ins_by_hour',
                'check_ins_by_ticket_type'
            ]);
    }

    public function test_regular_user_cannot_get_check_in_stats()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");

        $response->assertForbidden();
    }

    public function test_check_in_stats_are_included_in_event_analytics()
    {
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/analytics/my-events/{$this->event->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'general' => [
                    'check_in_rate'
                ],
                'check_in_times'
            ]);

        // Verify the check-in rate matches
        $this->assertEquals(75, $response->json('general.check_in_rate'));
    }

    public function test_check_in_time_distribution_is_accurate()
    {
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/check-in/events/{$this->event->id}/stats");

        $response->assertOk();

        $checkInsByHour = $response->json('check_ins_by_hour');

        // We should have check-ins distributed across 5 hours
        $uniqueHours = collect($checkInsByHour)->pluck('hour')->unique()->count();

        // Due to the way we set up the test data, we should have check-ins across at least 3 different hours
        $this->assertGreaterThanOrEqual(3, $uniqueHours);
    }
}
