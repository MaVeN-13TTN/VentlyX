<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $booking = Booking::factory()->create();
        return [
            'booking_id' => $booking->id,
            'payment_method' => $this->faker->randomElement(['stripe', 'paypal']),
            'amount' => $booking->total_price,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_id' => null,
            'transaction_details' => null,
        ];
    }

    /**
     * Configure the payment as completed with Stripe
     */
    public function completedWithStripe(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_method' => 'stripe',
                'status' => 'completed',
                'payment_id' => 'pi_' . $this->faker->regexify('[A-Za-z0-9]{24}'),
                'transaction_details' => [
                    'status' => 'succeeded',
                    'charges' => [
                        [
                            'id' => 'ch_' . $this->faker->regexify('[A-Za-z0-9]{24}'),
                            'amount' => $attributes['amount'] * 100,
                            'currency' => 'usd',
                            'status' => 'succeeded',
                        ]
                    ],
                    'created' => time(),
                ]
            ];
        });
    }

    /**
     * Configure the payment as completed with PayPal
     */
    public function completedWithPayPal(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_method' => 'paypal',
                'status' => 'completed',
                'payment_id' => $this->faker->regexify('[A-Z0-9]{12}'),
                'transaction_details' => [
                    'payer_id' => $this->faker->regexify('[A-Z0-9]{13}'),
                    'payer_email' => $this->faker->email(),
                    'transaction_id' => $this->faker->regexify('[A-Z0-9]{17}')
                ]
            ];
        });
    }

    /**
     * Configure the payment as refunded
     */
    public function refunded(): self
    {
        return $this->state(function (array $attributes) {
            $isStripe = $attributes['payment_method'] === 'stripe';
            $baseDetails = $isStripe ?
                $this->completedWithStripe()->getStateFor([]) :
                $this->completedWithPayPal()->getStateFor([]);

            $refundDetails = $isStripe ?
                [
                    'refund_id' => 're_' . $this->faker->regexify('[A-Za-z0-9]{24}'),
                    'amount' => $attributes['amount'],
                    'status' => 'succeeded',
                    'reason' => 'requested_by_customer',
                    'created' => time()
                ] :
                [
                    'refund_id' => $this->faker->regexify('[A-Z0-9]{17}'),
                    'amount' => $attributes['amount'],
                    'status' => 'COMPLETED',
                    'create_time' => now()->toIso8601String()
                ];

            return [
                'status' => 'refunded',
                'transaction_details' => array_merge(
                    $baseDetails['transaction_details'],
                    ['refund' => $refundDetails]
                )
            ];
        });
    }
}
