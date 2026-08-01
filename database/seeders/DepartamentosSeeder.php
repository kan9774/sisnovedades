<?php

namespace Database\Seeders;

use App\Models\Departamento;
use Illuminate\Database\Seeder;

class DepartamentosSeeder extends Seeder
{
    public function run(): void
    {
        $departamentos = [
            ['nombre' => 'Artigas', 'codigo_ine' => '01'],
            ['nombre' => 'Canelones', 'codigo_ine' => '02'],
            ['nombre' => 'Cerro Largo', 'codigo_ine' => '03'],
            ['nombre' => 'Colonia', 'codigo_ine' => '04'],
            ['nombre' => 'Durazno', 'codigo_ine' => '05'],
            ['nombre' => 'Flores', 'codigo_ine' => '06'],
            ['nombre' => 'Florida', 'codigo_ine' => '07'],
            ['nombre' => 'Lavalleja', 'codigo_ine' => '08'],
            ['nombre' => 'Maldonado', 'codigo_ine' => '09'],
            ['nombre' => 'Montevideo', 'codigo_ine' => '10'],
            ['nombre' => 'Paysandú', 'codigo_ine' => '11'],
            ['nombre' => 'Río Negro', 'codigo_ine' => '12'],
            ['nombre' => 'Rivera', 'codigo_ine' => '13'],
            ['nombre' => 'Rocha', 'codigo_ine' => '14'],
            ['nombre' => 'Salto', 'codigo_ine' => '15'],
            ['nombre' => 'San José', 'codigo_ine' => '16'],
            ['nombre' => 'Soriano', 'codigo_ine' => '17'],
            ['nombre' => 'Tacuarembó', 'codigo_ine' => '18'],
            ['nombre' => 'Treinta y Tres', 'codigo_ine' => '19'],
        ];

        foreach ($departamentos as $dep) {
            Departamento::firstOrCreate(['nombre' => $dep['nombre']], $dep);
        }
    }
}