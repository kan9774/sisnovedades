<?php

namespace Database\Seeders;

use App\Models\Paloma;
use App\Models\Vuelo;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VueloSeeder extends Seeder
{
    public function run(): void
    {
        $palomas = Paloma::whereIn('estado_id', function ($query) {
            $query->select('id')->from('estados_paloma')
                  ->whereIn('nombre', ['Activa', 'Reproductora']);
        })->get();

        if ($palomas->isEmpty()) {
            $this->command->warn('No hay palomas activas para asignar vuelos.');
            return;
        }

        $puntos = ['Peñarol', 'Montevideo', 'Canelones', 'Colonia'];
        $climas = ['Despejado', 'Nublado', 'Lluvia ligera', 'Viento fuerte'];

        $cantidadVuelos = 8; // eventos/sueltas a generar

        for ($i = 0; $i < $cantidadVuelos; $i++) {
            $fecha = Carbon::now()->subDays(rand(1, 30));
            $tipo = $i % 2 == 0 ? 'entrenamiento' : 'competicion';
            $horaSale = Carbon::createFromTime(rand(6, 9), rand(0, 59), 0);

            $vuelo = Vuelo::create([
                'fecha' => $fecha,
                'tipo' => $tipo,
                'punto_liberacion' => $puntos[array_rand($puntos)],
                'hora_liberacion' => $horaSale->format('H:i:s'),
                'condiciones_climaticas' => $climas[array_rand($climas)],
                'observaciones' => null,
            ]);

            // Elegir un grupo aleatorio de palomas para este vuelo (entre 1 y todas)
            $cantidadParticipantes = rand(1, min(20, $palomas->count()));
            $participantes = $palomas->random($cantidadParticipantes);
            if (!$participantes instanceof \Illuminate\Support\Collection) {
                $participantes = collect([$participantes]);
            }

            $pivotData = [];
            foreach ($participantes as $index => $paloma) {
                $horaLlega = clone $horaSale;
                $horaLlega->addHours(rand(2, 6))->addMinutes(rand(0, 59));

                $diff = $horaSale->diff($horaLlega);
                $horasTotales = $diff->h + ($diff->i / 60) + ($diff->s / 3600);
                $distanciaKm = rand(50, 500) + rand(0, 99) / 100;
                $velocidadMedia = $horasTotales > 0 ? round($distanciaKm / $horasTotales, 2) : null;

                $pivotData[$paloma->id] = [
                    'distancia_km' => $distanciaKm,
                    'hora_llegada' => $horaLlega->format('H:i:s'),
                    'tiempo_vuelo' => $diff->format('%H:%I:%S'),
                    'velocidad_media' => $velocidadMedia,
                    'posicion' => $tipo === 'competicion' ? ($index + 1) : null,
                    'anilla_competicion' => $tipo === 'competicion'
                        ? 'C-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)
                        : null,
                    'observaciones' => null,
                ];
            }

            $vuelo->palomas()->attach($pivotData);
        }

        $this->command->info('Vuelos de prueba creados.');
    }
}