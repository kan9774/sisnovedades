<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guard>
 */
class GuardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeThisMonth(),
            'captain_id' => User::factory(),
            'oficer_id' => User::factory(),
            'status' => fake()->randomElement(['open', 'closed']),
            'closed_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function abierta(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    public function cerrada(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'closed_at' => fake()->dateTime(),
        ]);
    }
}
