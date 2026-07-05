<?php

namespace Database\Seeders;

use App\Models\EstadoPaloma;
use Illuminate\Database\Seeder;

class EstadoPalomaSeeder extends Seeder
{
    public function run()
    {
        $estados = [
            ['nombre' => 'Activa', 'color' => 'success'],
            ['nombre' => 'Reproductora', 'color' => 'primary'],
            ['nombre' => 'Ausente', 'color' => 'warning'],
            ['nombre' => 'Vendida', 'color' => 'secondary'],
            ['nombre' => 'En préstamo', 'color' => 'info'],
            ['nombre' => 'Baja', 'color' => 'danger'],
            ['nombre' => 'En recuperación', 'color' => 'dark'],
        ];

        foreach ($estados as $estado) {
            EstadoPaloma::firstOrCreate(
                ['nombre' => $estado['nombre']],
                ['color' => $estado['color'], 'activo' => true]
            );
        }
    }
}