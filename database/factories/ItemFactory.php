<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->words(3, true),
            'categoria_id' => Categoria::factory(),
            'tipo_seguimiento' => fake()->randomElement(['cantidad', 'individual']),
            'unidad_medida' => fake()->randomElement(['UNIDAD', 'KG', 'LITRO', 'PAR']),
            'atributos' => [],
        ];
    }
}
