<?php

namespace Database\Seeders;

use App\Models\CategoriaDocumento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = ['Reglamentos', 'Manuales', 'Normativas', 'Circulares'];

        foreach ($categorias as $nombre) {
            CategoriaDocumento::firstOrCreate(
                ['slug' => Str::slug($nombre)],
                ['nombre' => $nombre]
            );
        }
    }
}
