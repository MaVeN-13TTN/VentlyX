<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Admin', 'Organizer', 'User']),
            'description' => $this->faker->sentence()
        ];
    }

    public function admin(): self
    {
        return $this->state([
            'name' => 'Admin',
            'description' => 'Administrator role with full access'
        ]);
    }

    public function organizer(): self
    {
        return $this->state([
            'name' => 'Organizer',
            'description' => 'Event organizer role'
        ]);
    }

    public function user(): self
    {
        return $this->state([
            'name' => 'User',
            'description' => 'Regular user role'
        ]);
    }
}
