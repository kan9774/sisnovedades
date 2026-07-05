<?php

namespace Database\Seeders;

use App\Models\Guard;
use App\Models\SalidaVehiculo;
use App\Models\Vehiculo;
use App\Models\Conductor;
use Illuminate\Database\Seeder;

class SalidaVehiculoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener una guardia existente (la primera o la de hoy)
        $guardia = Guard::first();
        if (!$guardia) {
            $this->command->warn('No hay guardias para asociar salidas. Ejecuta GuardiaSeeder primero.');
            return;
        }

        $vehiculo = Vehiculo::first();
        $conductor = Conductor::first();

        if (!$vehiculo || !$conductor) {
            $this->command->warn('Faltan vehículos o conductores. Ejecuta VehiculoConductorSeeder primero.');
            return;
        }

        $salidas = [
            [
                'guardia_id' => $guardia->id,
                'vehiculo_id' => $vehiculo->id,
                'conductor_id' => $conductor->id,
                'tipo_combustible' => 'gas_oil',
                'hora_sale' => '08:30',
                'hora_entra' => '12:15',
                'kms_sale' => 1500,
                'kms_entra' => 1580,
                'kms_recorridos' => 80,
                'litros' => 12.0,
                'consumo_usado' => 0.15,
                'comision' => 'Traslado de personal a zona de entrenamiento',
            ],
            [
                'guardia_id' => $guardia->id,
                'vehiculo_id' => $vehiculo->id,
                'conductor_id' => $conductor->id,
                'tipo_combustible' => 'nafta',
                'hora_sale' => '14:00',
                'hora_entra' => '17:45',
                'kms_sale' => 1580,
                'kms_entra' => 1650,
                'kms_recorridos' => 70,
                'litros' => 8.4,
                'consumo_usado' => 0.12,
                'comision' => 'Patrullaje rutinario',
            ],
        ];

        foreach ($salidas as $salida) {
            SalidaVehiculo::create($salida);
        }

        $this->command->info('Salidas de vehículos de prueba creadas.');
    }
}