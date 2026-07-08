<?php

namespace Database\Seeders;

use App\Models\Guard;
use App\Models\News;
use App\Models\Oficina;
use App\Models\User;
use Illuminate\Database\Seeder;

class NovedadesSeeder extends Seeder
{
    public function run(): void
    {
        $guardia = Guard::first();
        if (!$guardia) {
            $this->command->warn('No hay guardias. Ejecuta GuardiaSeeder primero.');
            return;
        }

        $escribiente = User::where('email', 'escribiente@sistema.com')->first();
        if (!$escribiente) {
            $this->command->warn('No hay escribientes.');
            return;
        }
        $Oficina = Oficina::firstOrCreate(['nombre' => 'Oficina de Com']);
        $novedades = [
            [
                'type' => 'Radio',
                'direction' => 'Recibido',
                'number' => '001',
                'time' => '08:30',
                'office_id' => $Oficina->id,
                'affair' => 'Aviso de ejercicio',
                'text' => 'Se recibe comunicación sobre ejercicio programado.',
                'clasification' => 'Prioritario',
                'destino' => null,
                'organismo_id' => null,
            ],
            [
                'type' => 'Correo Electrónico',
                'direction' => 'Expedido',
                'number' => '002',
                'time' => '10:15',
               'office_id' => $Oficina->id,
                'affair' => 'Solicitud de combustible',
                'text' => 'Se envía solicitud de combustible para vehículos.',
                'clasification' => 'Rutinario',
                'destino' => 'Comando de Logística',
                'organismo_id' => null,
            ],
        ];

        foreach ($novedades as $data) {
            News::create([
                ...$data,
                'guard_id' => $guardia->id,
                'user_id' => $escribiente->id,
                'confirmed' => false,
                'confirmed_at' => null,
            ]);
        }

        $this->command->info('Novedades de prueba creadas.');
    }
}