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

class PaymentAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $organizer;
    private User $user;
    private array $payments;

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

        // Create events, ticket types, bookings, and payments
        $this->createTestData();
    }

    private function createTestData(): void
    {
        $this->payments = [];

        // Create multiple events with different organizers
        $events = [
            Event::factory()->create([
                'organizer_id' => $this->organizer->id,
                'status' => 'published',
            ]),
            Event::factory()->create([
                'organizer_id' => $this->organizer->id,
                'status' => 'published',
            ]),
            Event::factory()->create([
                'organizer_id' => User::factory()->create()->id,
                'status' => 'published',
            ]),
        ];

        // Create ticket types for each event
        foreach ($events as $event) {
            $ticketType = TicketType::factory()->create([
                'event_id' => $event->id,
                'price' => rand(1000, 5000),
            ]);

            // Create successful payments
            for ($i = 0; $i < 5; $i++) {
                $booking = Booking::factory()->create([
                    'event_id' => $event->id,
                    'ticket_type_id' => $ticketType->id,
                    'user_id' => User::factory()->create()->id,
                    'quantity' => rand(1, 3),
                    'total_price' => $ticketType->price * rand(1, 3),
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);

                $payment = Payment::factory()->completed()->create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_price,
                    'payment_method' => ['stripe', 'mpesa', 'paypal'][rand(0, 2)],
                    'created_at' => $booking->created_at,
                ]);

                $this->payments[] = $payment;
            }

            // Create failed payments
            for ($i = 0; $i < 2; $i++) {
                $booking = Booking::factory()->create([
                    'event_id' => $event->id,
                    'ticket_type_id' => $ticketType->id,
                    'user_id' => User::factory()->create()->id,
                    'quantity' => rand(1, 3),
                    'total_price' => $ticketType->price * rand(1, 3),
                    'status' => 'pending',
                    'payment_status' => 'failed',
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);

                $payment = Payment::factory()->failed()->create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_price,
                    'payment_method' => ['stripe', 'mpesa', 'paypal'][rand(0, 2)],
                    'failure_reason' => ['insufficient_funds', 'card_declined', 'network_error'][rand(0, 2)],
                    'created_at' => $booking->created_at,
                ]);

                $this->payments[] = $payment;
            }

            // Create refunded payments
            for ($i = 0; $i < 1; $i++) {
                $booking = Booking::factory()->create([
                    'event_id' => $event->id,
                    'ticket_type_id' => $ticketType->id,
                    'user_id' => User::factory()->create()->id,
                    'quantity' => rand(1, 3),
                    'total_price' => $ticketType->price * rand(1, 3),
                    'status' => 'refunded',
                    'payment_status' => 'refunded',
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);

                $payment = Payment::factory()->refunded()->create([
                    'booking_id' => $booking->id,
                    'amount' => $booking->total_price,
                    'payment_method' => ['stripe', 'mpesa', 'paypal'][rand(0, 2)],
                    'created_at' => $booking->created_at,
                ]);

                $this->payments[] = $payment;
            }
        }
    }

    public function test_admin_can_access_payment_stats()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/analytics/payments');

        $response->assertOk()
            ->assertJsonStructure([
                'total_revenue',
                'successful_payments',
                'failed_payments',
                'refunded_amount',
                'payment_method_distribution',
                'daily_revenue',
                'monthly_revenue',
                'refund_analysis',
                'failure_reasons'
            ]);

        // Verify the counts match our test data
        $this->assertEquals(15, $response->json('successful_payments')); // 5 successful payments * 3 events
        $this->assertEquals(6, $response->json('failed_payments')); // 2 failed payments * 3 events
    }

    public function test_non_admin_cannot_access_payment_stats()
    {
        $response = $this->actingAs($this->organizer)
            ->getJson('/api/v1/analytics/payments');

        $response->assertForbidden();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/analytics/payments');

        $response->assertForbidden();
    }

    public function test_admin_can_access_payment_method_trends()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/analytics/payments/trends');

        $response->assertOk()
            ->assertJsonStructure([
                'trends' => [
                    '*' => [
                        'date',
                        'stripe',
                        'mpesa',
                        'paypal'
                    ]
                ]
            ]);
    }

    public function test_admin_can_access_payment_failure_analysis()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/analytics/payments/failures');

        $response->assertOk()
            ->assertJsonStructure([
                'analysis' => [
                    '*' => [
                        'failure_reason',
                        'payment_method',
                        'count',
                        'average_amount'
                    ]
                ]
            ]);
    }

    public function test_payment_stats_with_date_range_filtering()
    {
        $startDate = Carbon::now()->subDays(15)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/analytics/payments/failures?start_date={$startDate}&end_date={$endDate}");

        $response->assertOk()
            ->assertJsonStructure([
                'analysis'
            ]);
    }
}
