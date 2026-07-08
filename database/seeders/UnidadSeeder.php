<?php

namespace Database\Seeders;

use App\Models\Unidad;
use Illuminate\Database\Seeder;

class UnidadSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            'B.Com.N°1',
            'Bn.Com.N°1',
            'Bn.Com.N°2',
            'E.Com.E.',
        ];

        foreach ($unidades as $nombre) {
            Unidad::firstOrCreate(['nombre' => $nombre]);
        }
    }
}