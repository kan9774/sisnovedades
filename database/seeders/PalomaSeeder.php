<?php

namespace Database\Seeders;

use App\Models\Paloma;
use App\Models\Palomar;
use App\Models\EstadoPaloma;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PalomaSeeder extends Seeder
{
    public function run(): void
    {
        $palomar = Palomar::first();
        if (!$palomar) {
            $this->command->warn('No hay palomares. Ejecuta PalomarSeeder primero.');
            return;
        }

        $estados = EstadoPaloma::pluck('id', 'nombre');

        $palomas = [
            // Adultas
            [
                'anilla' => 'P-001',
                'nombre' => 'Paloma 1',
                'fecha_nacimiento' => Carbon::now()->subMonths(12),
                'sexo' => 'macho',
                'color' => 'Blanco',
                'raza' => 'Mensajera Belga',
                'origen' => 'Criadero Militar',
                'estado_id' => $estados['Activa'] ?? null,
                'observaciones' => null,
            ],
            [
                'anilla' => 'P-002',
                'nombre' => 'Paloma 2',
                'fecha_nacimiento' => Carbon::now()->subMonths(18),
                'sexo' => 'hembra',
                'color' => 'Barcino',
                'raza' => 'Mensajera Belga',
                'origen' => 'Criadero Militar',
                'estado_id' => $estados['Reproductora'] ?? null,
                'observaciones' => 'En periodo de cría',
            ],
            [
                'anilla' => 'P-003',
                'nombre' => 'Paloma 3',
                'fecha_nacimiento' => Carbon::now()->subMonths(24),
                'sexo' => 'macho',
                'color' => 'Azul',
                'raza' => 'Racing Pigeon',
                'origen' => 'Compra externa',
                'estado_id' => $estados['Ausente'] ?? null,
                'observaciones' => 'No ha regresado desde el 15JUN26',
            ],
            [
                'anilla' => 'P-004',
                'nombre' => 'Paloma 4',
                'fecha_nacimiento' => Carbon::now()->subMonths(8),
                'sexo' => 'hembra',
                'color' => 'Negro',
                'raza' => 'Mensajera Inglesa',
                'origen' => 'Criadero Militar',
                'estado_id' => $estados['En préstamo'] ?? null,
                'observaciones' => 'En préstamo a la Base Aérea N°2',
            ],
            // Pichones
            [
                'anilla' => 'P-005',
                'nombre' => 'Pichón 1',
                'fecha_nacimiento' => Carbon::now()->subMonths(3),
                'sexo' => 'desconocido',
                'color' => 'Blanco',
                'raza' => 'Mensajera Belga',
                'origen' => 'Criadero Militar',
                'estado_id' => $estados['Activa'] ?? null,
                'observaciones' => 'Nuevo pichón de P-001 y P-002',
            ],
            [
                'anilla' => 'P-006',
                'nombre' => 'Pichón 2',
                'fecha_nacimiento' => Carbon::now()->subMonths(2),
                'sexo' => 'desconocido',
                'color' => 'Barcino',
                'raza' => 'Mensajera Belga',
                'origen' => 'Criadero Militar',
                'estado_id' => $estados['Activa'] ?? null,
                'observaciones' => null,
            ],
            [
                'anilla' => 'P-007',
                'nombre' => 'Pichón 3',
                'fecha_nacimiento' => Carbon::now()->subMonths(1),
                'sexo' => 'desconocido',
                'color' => 'Azul',
                'raza' => 'Racing Pigeon',
                'origen' => 'Compra externa',
                'estado_id' => $estados['Activa'] ?? null,
                'observaciones' => 'En proceso de adaptación',
            ],
            // Baja (no se cuenta en existencias)
            [
                'anilla' => 'P-008',
                'nombre' => 'Paloma 5',
                'fecha_nacimiento' => Carbon::now()->subMonths(30),
                'sexo' => 'hembra',
                'color' => 'Blanco',
                'raza' => 'Mensajera Belga',
                'origen' => 'Criadero Militar',
                'estado_id' => $estados['Baja'] ?? null,
                'observaciones' => 'Fallecida por enfermedad',
            ],
            // Vendida
            [
                'anilla' => 'P-009',
                'nombre' => 'Paloma 6',
                'fecha_nacimiento' => Carbon::now()->subMonths(14),
                'sexo' => 'macho',
                'color' => 'Barcino',
                'raza' => 'Mensajera Belga',
                'origen' => 'Criadero Militar',
                'estado_id' => $estados['Vendida'] ?? null,
                'observaciones' => 'Vendida a criador particular',
            ],
        ];

        foreach ($palomas as $data) {
            $data['palomar_id'] = $palomar->id;
            Paloma::firstOrCreate(
                ['anilla' => $data['anilla']],
                $data
            );
        }

        $this->command->info('Palomas de prueba creadas.');
    }
}