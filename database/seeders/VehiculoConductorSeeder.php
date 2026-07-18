<?php

namespace Database\Seeders;

use App\Models\Vehiculo;
use App\Models\Conductor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculoConductorSeeder extends Seeder
{
    public function run(): void
    {
        // Los tipos de combustible ya vienen precargados por la migración
        // 2026_07_14_183333_migrate_tipo_combustible_to_catalogo.php
        $gasOilId = DB::table('tipos_combustible')->where('nombre', 'Gas Oil')->value('id');
        $naftaId  = DB::table('tipos_combustible')->where('nombre', 'Nafta')->value('id');

        // --- Vehículos ---
        $vehiculos = [
            [
                'matricula' => 'ENM-0685',
                'tipo_combustible_id' => $gasOilId,
                'consumo_litros_por_km' => 0.15,
                'sin_cuentakilometros' => false,
                'descripcion' => 'Camión Mercedes Benz',
                'activo' => true,
            ],
            [
                'matricula' => 'ENM-0724',
                'tipo_combustible_id' => $naftaId,
                'consumo_litros_por_km' => 0.12,
                'sin_cuentakilometros' => false,
                'descripcion' => 'Toyota Hilux 4x4',
                'activo' => true,
            ],
            [
                'matricula' => 'ENM-0891',
                'tipo_combustible_id' => $gasOilId,
                'consumo_litros_por_km' => 0.18,
                'sin_cuentakilometros' => true,
                'descripcion' => 'Camión sin cuentakm (Unimog)',
                'activo' => true,
            ],
        ];

        foreach ($vehiculos as $vehiculo) {
            Vehiculo::firstOrCreate(
                ['matricula' => $vehiculo['matricula']],
                $vehiculo
            );
        }

        // --- Conductores ---
        $conductores = [
            [
                'grado' => 'Sgto.(EC)',
                'primer_nombre' => 'Carlos',
                'segundo_nombre' => null,
                'primer_apellido' => 'Pereyra',
                'segundo_apellido' => null,
                'documento' => '12345678',
                'nro_licencia' => 'LIC-001',
                'categoria_licencia' => 'B',
                'fecha_vencimiento_licencia' => '2027-12-31',
                'lugar_carne_salud' => 'Hospital Militar',
                'fecha_vencimiento_carne_salud' => '2027-06-30',
                'lugar_carne_habilitante' => 'Escuela de Conductores',
                'fecha_vencimiento_carne_habilitante' => '2027-09-30',
                'tipo_vehiculo_habilitado' => 'Camión, Jeep',
                'observaciones' => null,
                'activo' => true,
            ],
            [
                'grado' => 'Cabo 1ª',
                'primer_nombre' => 'Juan',
                'segundo_nombre' => 'Manuel',
                'primer_apellido' => 'Rodríguez',
                'segundo_apellido' => null,
                'documento' => '87654321',
                'nro_licencia' => 'LIC-002',
                'categoria_licencia' => 'C',
                'fecha_vencimiento_licencia' => '2026-12-31',
                'lugar_carne_salud' => 'Hospital Militar',
                'fecha_vencimiento_carne_salud' => '2026-06-30',
                'lugar_carne_habilitante' => null,
                'fecha_vencimiento_carne_habilitante' => null,
                'tipo_vehiculo_habilitado' => 'Camión',
                'observaciones' => 'Pendiente renovación carné habilitante',
                'activo' => true,
            ],
            [
                'grado' => 'Sdo.1ra',
                'primer_nombre' => 'María',
                'segundo_nombre' => 'Luz',
                'primer_apellido' => 'González',
                'segundo_apellido' => 'Martínez',
                'documento' => '11223344',
                'nro_licencia' => 'LIC-003',
                'categoria_licencia' => 'B',
                'fecha_vencimiento_licencia' => '2028-06-30',
                'lugar_carne_salud' => 'Centro Médico Militar',
                'fecha_vencimiento_carne_salud' => '2028-06-30',
                'lugar_carne_habilitante' => 'Escuela de Conductores',
                'fecha_vencimiento_carne_habilitante' => '2027-12-31',
                'tipo_vehiculo_habilitado' => 'Jeep',
                'observaciones' => null,
                'activo' => true,
            ],
        ];

        foreach ($conductores as $conductor) {
            Conductor::firstOrCreate(
                ['documento' => $conductor['documento']],
                $conductor
            );
        }

        $this->command->info('Vehículos y conductores de prueba creados.');
    }
}