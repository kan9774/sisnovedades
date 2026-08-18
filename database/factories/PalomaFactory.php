<?php

namespace Database\Factories;

use App\Models\EstadoPaloma;
use App\Models\Palomar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Paloma>
 */
class PalomaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'palomar_id' => Palomar::factory(),
            'anilla' => strtoupper(fake()->bothify('UY-####-???')),
            'estado_id' => EstadoPaloma::factory(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }

    public function pichon(): self
    {
        return $this->state(fn (array $attributes) => [
            'fecha_nacimiento' => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    public function adulta(): self
    {
        return $this->state(fn (array $attributes) => [
            'fecha_nacimiento' => fake()->dateTimeBetween('-3 years', '-7 months'),
        ]);
    }
}
