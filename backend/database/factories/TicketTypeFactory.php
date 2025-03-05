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
        return [
            'event_id' => Event::factory(),
            'name' => $this->faker->randomElement(['General Admission', 'VIP', 'Premium', 'Early Bird', 'Student', 'Group']),
            'description' => $this->faker->paragraph(1),
            'price' => $this->faker->randomFloat(2, 10, 300),
            'quantity' => $this->faker->numberBetween(50, 500),
            'max_per_order' => $this->faker->numberBetween(4, 10),
            'sales_start_date' => now()->subDays(5),
            'sales_end_date' => now()->addDays(10),
        ];
    }

    /**
     * Configure the ticket type to be sold out.
     */
    public function soldOut(): self
    {
        return $this->state([
            'quantity' => 0,
        ]);
    }

    /**
     * Configure the ticket type with limited availability.
     */
    public function limitedAvailability(int $remaining = 5): self
    {
        return $this->state([
            'quantity' => $remaining,
        ]);
    }

    /**
     * Configure the ticket type with sales period ended.
     */
    public function salesEnded(): self
    {
        return $this->state([
            'sales_end_date' => now()->subDay(),
        ]);
    }
}
