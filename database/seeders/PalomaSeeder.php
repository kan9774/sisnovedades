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
                'fecha_nacimiento' => Carbon::now()->subMonths(12),
                'sexo' => 'macho',
                'estado_id' => $estados['Activa'] ?? null,
                'observaciones' => null,
            ],
            [
                'anilla' => 'P-002',
                'fecha_nacimiento' => Carbon::now()->subMonths(18),
                'sexo' => 'hembra',
                'estado_id' => $estados['Reproductora'] ?? null,
                'observaciones' => 'En periodo de cría',
            ],
            [
                'anilla' => 'P-003',
                'fecha_nacimiento' => Carbon::now()->subMonths(24),
                'sexo' => 'macho',
                'estado_id' => $estados['Ausente'] ?? null,
                'observaciones' => 'No ha regresado desde el 15JUN26',
            ],
            [
                'anilla' => 'P-004',
                'fecha_nacimiento' => Carbon::now()->subMonths(8),
                'sexo' => 'hembra',
                'estado_id' => $estados['En préstamo'] ?? null,
                'observaciones' => 'En préstamo a la Base Aérea N°2',
            ],
            // Pichones
            [
                'anilla' => 'P-005',
                'fecha_nacimiento' => Carbon::now()->subMonths(3),
                'sexo' => 'desconocido',
                'estado_id' => $estados['Activa'] ?? null,
                'observaciones' => 'Nuevo pichón',
            ],
            [
                'anilla' => 'P-006',
                'fecha_nacimiento' => Carbon::now()->subMonths(2),
                'sexo' => 'desconocido',
                'estado_id' => $estados['Activa'] ?? null,
                'observaciones' => null,
            ],
            [
                'anilla' => 'P-007',
                'fecha_nacimiento' => Carbon::now()->subMonths(1),
                'sexo' => 'desconocido',
                'estado_id' => $estados['Activa'] ?? null,
                'observaciones' => 'En proceso de adaptación',
            ],
            // Baja
            [
                'anilla' => 'P-008',
                'fecha_nacimiento' => Carbon::now()->subMonths(30),
                'sexo' => 'hembra',
                'estado_id' => $estados['Baja'] ?? null,
                'observaciones' => 'Fallecida por enfermedad',
            ],
            // Vendida
            [
                'anilla' => 'P-009',
                'fecha_nacimiento' => Carbon::now()->subMonths(14),
                'sexo' => 'macho',
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