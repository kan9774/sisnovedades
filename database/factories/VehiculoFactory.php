<?php

namespace Database\Factories;

use App\Models\TipoCombustible;
use App\Models\TipoLubricante;
use App\Models\TipoRodado;
use App\Models\TipoVehiculo;
use App\Models\Unidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehiculo>
 */
class VehiculoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'matricula' => 'VH-' . strtoupper(fake()->unique()->bothify('??###')),
            'marca' => fake()->randomElement(['Ford', 'Chevrolet', 'Toyota', 'Iveco', 'Mercedes-Benz']),
            'modelo' => fake()->word(),
            'tipo_vehiculo_id' => TipoVehiculo::inRandomOrder()->value('id') ?? TipoVehiculo::factory()->create()->id,
            'unidad_id' => Unidad::inRandomOrder()->value('id') ?? Unidad::factory()->create()->id,
            'tipo_combustible_id' => TipoCombustible::inRandomOrder()->value('id') ?? TipoCombustible::factory()->create()->id,
            'tipo_lubricante_id' => TipoLubricante::inRandomOrder()->value('id') ?? TipoLubricante::factory()->create()->id,
            'tipo_rodado_id' => TipoRodado::inRandomOrder()->value('id') ?? TipoRodado::factory()->create()->id,
            'consumo_litros_por_km' => fake()->randomFloat(2, 0.1, 1.0),
            'activo' => true,
        ];
    }
}
