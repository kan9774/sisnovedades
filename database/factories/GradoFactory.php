<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grado>
 */
class GradoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement(['Soldado', 'Cabo', 'Sargento Tercero', 'Sargento Segundo', 'Sargento Primero', 'Suboficial', 'Teniente', 'Capitan', 'Mayor', 'Teniente Coronel', 'Coronel']),
        ];
    }
}
