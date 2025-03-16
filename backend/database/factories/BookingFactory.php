<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'ticket_type_id' => TicketType::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'total_price' => $this->faker->numberBetween(1000, 10000),
            'status' => 'pending',
            'payment_status' => 'pending',
            'booking_reference' => 'BK-' . $this->faker->regexify('[A-Za-z0-9]{10}'),
            'qr_code_url' => null,
            'transfer_code' => null,
            'transfer_status' => null,
            'transfer_initiated_at' => null,
            'transfer_completed_at' => null,
            'checked_in_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the booking is confirmed.
     */
    public function confirmed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ];
        });
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
            ];
        });
    }

    /**
     * Indicate that the booking is checked in.
     */
    public function checkedIn()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'checked_in_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the booking has a pending transfer.
     */
    public function pendingTransfer()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'transfer_code' => strtoupper(Str::random(8)),
                'transfer_status' => 'pending',
                'transfer_initiated_at' => now(),
            ];
        });
    }
}
