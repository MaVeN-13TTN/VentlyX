<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ticketType = TicketType::factory()->create();
        $quantity = $this->faker->numberBetween(1, 5);

        return [
            'user_id' => User::factory(),
            'event_id' => $ticketType->event_id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => $quantity,
            'total_price' => $quantity * $ticketType->price,
            'status' => 'pending',
            'payment_status' => 'pending',
            'qr_code_url' => null,
            'checked_in_at' => null,
        ];
    }

    /**
     * Configure the booking as confirmed/paid.
     */
    public function confirmed(): self
    {
        return $this->state([
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Configure the booking as cancelled.
     */
    public function cancelled(): self
    {
        return $this->state([
            'status' => 'cancelled',
            'payment_status' => 'cancelled',
        ]);
    }

    /**
     * Configure the booking as refunded.
     */
    public function refunded(): self
    {
        return $this->state([
            'status' => 'refunded',
            'payment_status' => 'refunded',
        ]);
    }

    /**
     * Configure the booking as checked in.
     */
    public function checkedIn(): self
    {
        return $this->state([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'checked_in_at' => now(),
        ]);
    }
}
