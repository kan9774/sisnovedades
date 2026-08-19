<?php
// database/migrations/2026_08_18_000002_change_destino_to_json_safe.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // 1. Agregar nueva columna destino_json
        if ($driver === 'sqlite') {
            Schema::table('news', function (Blueprint $table) {
                $table->text('destino_json')->nullable()->after('destino');
            });
        } elseif ($driver === 'pgsql') {
            Schema::table('news', function (Blueprint $table) {
                $table->jsonb('destino_json')->nullable()->after('destino');
            });
        } else {
            // MySQL
            Schema::table('news', function (Blueprint $table) {
                $table->json('destino_json')->nullable()->after('destino');
            });
        }

        // 2. Migrar datos existentes de manera segura
        $this->migrarDatos();

        // 3. Eliminar columna antigua y renombrar la nueva
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('destino');
            $table->renameColumn('destino_json', 'destino');
        });
    }

    private function migrarDatos(): void
    {
        $driver = DB::getDriverName();
        
        // Obtener todos los registros
        $registros = DB::table('news')
            ->select('id', 'destino')
            ->whereNotNull('destino')
            ->get();

        foreach ($registros as $registro) {
            $destinoArray = $this->decodificarComoArray($registro->destino);
            $valorJson = json_encode($destinoArray);

            DB::table('news')
                ->where('id', $registro->id)
                ->update(['destino_json' => $valorJson]);
        }
    }

    /**
     * Decodifica un valor legacy de destino y lo normaliza a array.
     *
     * Maneja tres formatos legacy:
     * 1. JSON array válido: ["Batallon 1", "Batallon 2"] → se mantiene
     * 2. JSON escalar válido (string, int, bool): "105" → ["105"]
     * 3. String simple o con comas: "Batallon 1, Batallon 2" → ["Batallon 1", "Batallon 2"]
     *
     * @see Bug 1 — escalares JSON-válidos no son array
     * @see Bug 2 — multi-destino legacy separado por coma
     */
    private function decodificarComoArray($valor): array
    {
        $decoded = json_decode($valor, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Bug 2: si contiene comas, separar en múltiples destinos
        if (is_string($valor) && str_contains($valor, ',')) {
            return array_map('trim', explode(',', $valor));
        }

        // Bug 1: escalar JSON-válido o string simple → wrap en array
        return [(string) $valor];
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        // 1. Agregar columna temporal destino_old
        Schema::table('news', function (Blueprint $table) {
            $table->string('destino_old')->nullable()->after('destino');
        });

        // 2. Convertir JSON array → primer elemento (pérdida de multi-destino en rollback)
        $registros = DB::table('news')
            ->select('id', 'destino')
            ->whereNotNull('destino')
            ->get();

        foreach ($registros as $registro) {
            $decodificado = json_decode($registro->destino, true);
            $valorOld = is_array($decodificado) && !empty($decodificado)
                ? $decodificado[0]
                : null;

            DB::table('news')
                ->where('id', $registro->id)
                ->update(['destino_old' => $valorOld]);
        }

        // 3. Eliminar columna destino y renombrar destino_old
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('destino');
            $table->renameColumn('destino_old', 'destino');
        });
    }
};