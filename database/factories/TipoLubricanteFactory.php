<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TipoLubricante>
 */
class TipoLubricanteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement(['Motriz 15W40', 'Hidraulico ATF', 'Hipoteico 80W90', 'Grasa Lithium']),
        ];
    }
}
