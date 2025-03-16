<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'ticket_type_id' => TicketType::factory(),
            'qr_code' => 'QR-' . Str::random(10),
            'status' => 'issued',
            'check_in_status' => 'not_checked_in',
            'checked_in_at' => null,
            'checked_in_by' => null,
            'seat_number' => $this->faker->optional()->numerify('A##'),
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the ticket has been checked in.
     */
    public function checkedIn(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'check_in_status' => 'checked_in',
                'checked_in_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the ticket has been cancelled.
     */
    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }

    /**
     * Indicate that the ticket has been refunded.
     */
    public function refunded(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'refunded',
            ];
        });
    }

    /**
     * Indicate that the ticket has expired.
     */
    public function expired(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'expired',
            ];
        });
    }
}
