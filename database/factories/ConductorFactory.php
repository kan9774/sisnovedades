<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conductor>
 */
class ConductorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'grado' => fake()->randomElement(['Suboficial', 'Cabo', 'Sargento', 'Teniente']),
            'primer_nombre' => fake()->firstName(),
            'segundo_nombre' => fake()->optional()->firstName(),
            'primer_apellido' => fake()->lastName(),
            'segundo_apellido' => fake()->optional()->lastName(),
            'documento' => fake()->bothify('########'),
            'nro_licencia' => fake()->bothify('LIC-######'),
            'categoria_licencia' => fake()->randomElement(['A', 'B', 'C']),
            'fecha_vencimiento_licencia' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'lugar_carne_salud' => fake()->optional()->city(),
            'fecha_vencimiento_carne_salud' => fake()->optional()->dateTimeBetween('-6 months', '+6 months'),
            'lugar_carne_habilitante' => fake()->optional()->city(),
            'numero_carne_habilitante' => fake()->optional()->bothify('CH-####'),
            'fecha_vencimiento_carne_habilitante' => fake()->optional()->dateTimeBetween('-6 months', '+6 months'),
            'tipo_vehiculo_habilitado' => fake()->optional()->word(),
            'observaciones' => fake()->optional()->sentence(),
            'activo' => true,
        ];
    }
}
