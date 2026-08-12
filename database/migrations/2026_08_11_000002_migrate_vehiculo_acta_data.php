<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $vehiculos = DB::table('vehiculos')->whereNotNull('acta')->where('acta', '!=', '')->get();

        $importados = 0;
        $omitidos = 0;

        foreach ($vehiculos as $vehiculo) {
            $path = $vehiculo->acta;

            if (! Storage::disk('public')->exists($path)) {
                Log::warning('Migración vehiculo_actas: archivo no encontrado en disco, se omite.', [
                    'vehiculo_id' => $vehiculo->id,
                    'path' => $path,
                ]);
                $omitidos++;
                continue;
            }

            $tamanoBytes = Storage::disk('public')->size($path);
            $nombreOriginal = basename($path);

            DB::table('vehiculo_actas')->insert([
                'vehiculo_id' => $vehiculo->id,
                'path' => $path,
                'nombre_original' => $nombreOriginal,
                'tamano_bytes' => $tamanoBytes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $importados++;
        }

        Log::info('Migración vehiculo_actas completada.', [
            'total_vehiculos' => $vehiculos->count(),
            'importados' => $importados,
            'omitidos' => $omitidos,
        ]);
    }

    public function down(): void
    {
        DB::table('vehiculo_actas')->truncate();
    }
};
