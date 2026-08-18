<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Departamento>
 */
class DepartamentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement(['Canelones', 'Maldonado', 'Paysandu', 'Rivera', 'Tacuarembo', 'Colonia', 'San Jose', 'Soriano']),
            'codigo_ine' => fake()->numerify('##'),
        ];
    }
}
