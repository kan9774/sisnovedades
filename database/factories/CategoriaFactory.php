<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->word(),
            'categoria_padre_id' => null,
        ];
    }

    public function hija(?int $parentId = null): self
    {
        return $this->state(fn (array $attributes) => [
            'categoria_padre_id' => $parentId,
        ]);
    }
}
