<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'payment_method' => $this->faker->randomElement(['stripe', 'mpesa', 'paypal']),
            'payment_id' => $this->faker->uuid(),
            'amount' => $this->faker->numberBetween(1000, 50000),
            'status' => 'pending',
            'transaction_details' => [
                'payment_intent' => $this->faker->uuid(),
                'customer_email' => $this->faker->email()
            ],
            'currency' => $this->faker->randomElement(['KES', 'USD', 'EUR']),
            'transaction_id' => strtoupper(uniqid()),
            'transaction_reference' => 'TX-' . $this->faker->regexify('[A-Za-z0-9]{10}'),
            'payment_date' => null,
            'failure_reason' => null,
            'refund_date' => null,
            'refund_reason' => null
        ];
    }

    public function completed(): self
    {
        return $this->state([
            'status' => 'completed',
            'payment_date' => now()
        ]);
    }

    public function failed(): self
    {
        return $this->state([
            'status' => 'failed',
            'failure_reason' => $this->faker->sentence()
        ]);
    }

    public function refunded(): self
    {
        return $this->state([
            'status' => 'refunded',
            'payment_date' => now()->subDays(2),
            'refund_date' => now(),
            'refund_reason' => $this->faker->sentence()
        ]);
    }

    public function mpesa(): self
    {
        return $this->state([
            'payment_method' => 'mpesa',
            'currency' => 'KES'
        ]);
    }

    public function stripe(): self
    {
        return $this->state([
            'payment_method' => 'stripe',
            'currency' => 'USD'
        ]);
    }
}
