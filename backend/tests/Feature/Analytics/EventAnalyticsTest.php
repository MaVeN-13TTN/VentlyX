<?php

namespace Tests\Feature\Analytics;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Role;
use App\Models\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventAnalyticsTest extends TestCase
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
            'start_time' => Carbon::now()->addDay(),
            'end_time' => Carbon::now()->addDay()->addHours(3),
        ]);

        // Create ticket types
        $this->ticketTypes = [
            TicketType::factory()->create([
                'event_id' => $this->event->id,
                'name' => 'VIP',
                'price' => 5000,
                'quantity' => 50,
                'tickets_remaining' => 40,
            ]),
            TicketType::factory()->create([
                'event_id' => $this->event->id,
                'name' => 'Regular',
                'price' => 2000,
                'quantity' => 100,
                'tickets_remaining' => 80,
            ]),
        ];

        // Create bookings with different statuses
        $this->bookings = [];

        // Confirmed bookings for VIP tickets
        for ($i = 0; $i < 5; $i++) {
            $booking = Booking::factory()->create([
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketTypes[0]->id,
                'user_id' => User::factory()->create()->id,
                'quantity' => 2,
                'total_price' => 10000,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'created_at' => Carbon::now()->subDays(rand(1, 10)),
            ]);

            // Create payment for the booking
            Payment::factory()->completed()->create([
                'booking_id' => $booking->id,
                'amount' => 10000,
                'payment_method' => rand(0, 1) ? 'stripe' : 'mpesa',
            ]);

            // Check in some bookings
            if ($i < 3) {
                $booking->update([
                    'checked_in_at' => Carbon::now(),
                    'checked_in_by' => $this->organizer->id,
                ]);
            }

            $this->bookings[] = $booking;
        }

        // Confirmed bookings for Regular tickets
        for ($i = 0; $i < 10; $i++) {
            $booking = Booking::factory()->create([
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketTypes[1]->id,
                'user_id' => User::factory()->create()->id,
                'quantity' => 2,
                'total_price' => 4000,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'created_at' => Carbon::now()->subDays(rand(1, 10)),
            ]);

            // Create payment for the booking
            Payment::factory()->completed()->create([
                'booking_id' => $booking->id,
                'amount' => 4000,
                'payment_method' => rand(0, 1) ? 'stripe' : 'mpesa',
            ]);

            // Check in some bookings
            if ($i < 5) {
                $booking->update([
                    'checked_in_at' => Carbon::now(),
                    'checked_in_by' => $this->organizer->id,
                ]);
            }

            $this->bookings[] = $booking;
        }

        // Cancelled bookings
        for ($i = 0; $i < 3; $i++) {
            $booking = Booking::factory()->create([
                'event_id' => $this->event->id,
                'ticket_type_id' => $this->ticketTypes[rand(0, 1)]->id,
                'user_id' => User::factory()->create()->id,
                'quantity' => 1,
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
                'created_at' => Carbon::now()->subDays(rand(1, 10)),
            ]);

            $this->bookings[] = $booking;
        }
    }

    public function test_organizer_can_access_event_stats()
    {
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/analytics/my-events/{$this->event->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'general' => [
                    'total_bookings',
                    'confirmed_bookings',
                    'cancelled_bookings',
                    'total_tickets_sold',
                    'total_revenue',
                    'check_in_rate',
                    'conversion_rate',
                    'capacity_utilization'
                ],
                'ticket_types',
                'sales_trends' => [
                    'daily',
                    'hourly'
                ],
                'payment_methods',
                'booking_sources',
                'demographics',
                'refund_data',
                'check_in_times'
            ]);

        // Verify some of the stats
        $this->assertEquals(18, $response->json('general.total_bookings'));
        $this->assertEquals(15, $response->json('general.confirmed_bookings'));
        $this->assertEquals(3, $response->json('general.cancelled_bookings'));
        $this->assertEquals(30, $response->json('general.total_tickets_sold')); // 15 confirmed bookings * 2 quantity
        $this->assertEquals(90000, $response->json('general.total_revenue')); // (5 VIP * 10000) + (10 Regular * 4000)

        // Check-in rate should be 8/15 = 53.33%
        $this->assertEqualsWithDelta(53.33, $response->json('general.check_in_rate'), 0.01);
    }

    public function test_admin_can_access_event_stats()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/analytics/events/{$this->event->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'general' => [
                    'total_bookings',
                    'confirmed_bookings',
                    'cancelled_bookings',
                    'total_tickets_sold',
                    'total_revenue',
                    'check_in_rate',
                    'conversion_rate',
                    'capacity_utilization'
                ],
                'ticket_types',
                'sales_trends',
                'payment_methods',
                'booking_sources',
                'demographics',
                'refund_data',
                'check_in_times'
            ]);
    }

    public function test_regular_user_cannot_access_event_stats()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/analytics/events/{$this->event->id}");

        $response->assertForbidden();
    }

    public function test_organizer_cannot_access_other_organizers_event_stats()
    {
        $otherOrganizer = User::factory()->create();
        $organizerRole = Role::where('name', 'Organizer')->first();
        $otherOrganizer->roles()->attach($organizerRole);

        $response = $this->actingAs($otherOrganizer)
            ->getJson("/api/v1/analytics/my-events/{$this->event->id}");

        $response->assertForbidden();
    }

    public function test_event_stats_with_date_range_filtering()
    {
        // Create some bookings with specific dates
        $startDate = Carbon::now()->subDays(5)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/analytics/my-events/{$this->event->id}?start_date={$startDate}&end_date={$endDate}");

        $response->assertOk();

        // We can't assert exact values here since the test data has random dates
        // But we can verify the structure and that the response is successful
    }

    public function test_organizer_can_access_dashboard_stats()
    {
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/analytics/organizer-dashboard");

        $response->assertOk()
            ->assertJsonStructure([
                'events_summary' => [
                    'total_events',
                    'published_events',
                    'draft_events',
                    'upcoming_events'
                ],
                'sales_summary' => [
                    'total_sales',
                    'total_tickets_sold',
                    'average_ticket_price'
                ],
                'popular_events',
                'sales_by_ticket_type',
                'sales_by_day',
                'check_in_stats'
            ]);
    }

    public function test_admin_can_access_overall_stats()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/analytics/overall");

        $response->assertOk()
            ->assertJsonStructure([
                'total_events',
                'published_events',
                'upcoming_events',
                'total_sales',
                'total_tickets_sold',
                'popular_categories',
                'popular_events',
                'daily_revenue'
            ]);
    }

    public function test_non_admin_cannot_access_overall_stats()
    {
        $response = $this->actingAs($this->organizer)
            ->getJson("/api/v1/analytics/overall");

        $response->assertForbidden();
    }
}
