<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $endTime = clone $startTime;
        $endTime->modify('+3 hours');

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'location' => $this->faker->city(),
            'venue' => $this->faker->company() . ' ' . $this->faker->randomElement(['Arena', 'Center', 'Hall', 'Auditorium']),
            'organizer_id' => User::factory(),
            'category' => $this->faker->randomElement(['concert', 'conference', 'workshop', 'exhibition', 'sports', 'festival']),
            'status' => 'published',
            'featured' => $this->faker->boolean(20),
            'max_capacity' => $this->faker->numberBetween(50, 1000),
            'image_url' => $this->faker->imageUrl(800, 600, 'events')
        ];
    }

    /**
     * Set the event status to draft.
     */
    public function draft(): self
    {
        return $this->state([
            'status' => 'draft'
        ]);
    }

    /**
     * Set the event status to published.
     */
    public function published(): self
    {
        return $this->state([
            'status' => 'published'
        ]);
    }

    /**
     * Set the event status to cancelled.
     */
    public function cancelled(): self
    {
        return $this->state([
            'status' => 'cancelled'
        ]);
    }

    /**
     * Set the event as featured.
     */
    public function featured(): self
    {
        return $this->state([
            'featured' => true
        ]);
    }

    /**
     * Set the event as ended (in the past).
     */
    public function ended(): self
    {
        $startTime = $this->faker->dateTimeBetween('-30 days', '-2 days');
        $endTime = clone $startTime;
        $endTime->modify('+3 hours');

        return $this->state([
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }

    /**
     * Set the event status to archived.
     */
    public function archived(): self
    {
        return $this->state([
            'status' => 'archived'
        ]);
    }
}
