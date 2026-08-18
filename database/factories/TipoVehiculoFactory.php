<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TipoVehiculo>
 */
class TipoVehiculoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->randomElement(['Camioneta', 'Camion', 'Automovil', 'Motocicleta', 'Remolque', 'Camioneta de Combate']),
            'activo' => true,
        ];
    }
}
