<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketType>
 */
class TicketTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TicketType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(50, 200);

        return [
            'event_id' => Event::factory(),
            'name' => $this->faker->randomElement(['Regular', 'VIP', 'VVIP', 'Early Bird', 'Group']),
            'price' => $this->faker->numberBetween(1000, 10000),
            'quantity' => $quantity,
            'description' => $this->faker->paragraph(),
            'max_per_order' => $this->faker->numberBetween(2, 10),
            'sales_start_date' => $this->faker->dateTimeBetween('now', '+1 week'),
            'sales_end_date' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
            'is_available' => true,
            'tickets_remaining' => $quantity,
            'status' => 'active'
        ];
    }

    /**
     * Configure the ticket type to be sold out.
     */
    public function soldOut(): self
    {
        return $this->state([
            'tickets_remaining' => 0,
            'status' => 'sold_out'
        ]);
    }

    /**
     * Configure the ticket type with draft status.
     */
    public function draft(): self
    {
        return $this->state([
            'status' => 'draft'
        ]);
    }

    /**
     * Configure the ticket type with paused status.
     */
    public function paused(): self
    {
        return $this->state([
            'status' => 'paused'
        ]);
    }

    /**
     * Configure the ticket type with expired status.
     */
    public function expired(): self
    {
        return $this->state([
            'sales_end_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'status' => 'expired'
        ]);
    }
}
