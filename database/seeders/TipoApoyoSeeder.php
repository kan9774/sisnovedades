<?php

namespace Database\Seeders;

use App\Models\TipoApoyo;
use Illuminate\Database\Seeder;

class TipoApoyoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Vehículos', 'color' => '#28a745'],
            ['nombre' => 'Amplificación', 'color' => '#007bff'],
            ['nombre' => 'Antenistas', 'color' => '#dc3545'],
        ];

        foreach ($tipos as $tipo) {
            TipoApoyo::firstOrCreate(
                ['nombre' => $tipo['nombre']],
                ['color' => $tipo['color']]
            );
        }
    }
}
