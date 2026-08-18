<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EstadoPaloma>
 */
class EstadoPalomaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement(['Activa', 'Inactiva', 'En cuarentena', 'Entregada', 'Muerta', 'Perdida']),
            'color' => fake()->randomElement(['green', 'red', 'orange', 'gray', 'black']),
            'activo' => true,
        ];
    }
}
