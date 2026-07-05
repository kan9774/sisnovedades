<?php

namespace Database\Seeders;

use App\Models\Palomar;
use Illuminate\Database\Seeder;

class PalomarSeeder extends Seeder
{
    public function run()
    {
        $palomares = [
            ['nombre' => 'Palomar Principal', 'ubicacion' => 'Cuartel Peñarol', 'capacidad_maxima' => 200],
            ['nombre' => 'Palomar Norte', 'ubicacion' => 'Base Aérea N°2', 'capacidad_maxima' => 150],
        ];

        foreach ($palomares as $data) {
            Palomar::firstOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }
    }
}