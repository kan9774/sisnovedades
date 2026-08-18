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
            $valor = $registro->destino;
            $valorJson = null;

            if (!empty($valor)) {
                // Si ya es un array JSON, mantenerlo
                if ($this->esJson($valor)) {
                    $valorJson = $valor;
                } else {
                    // Si es un string simple, convertirlo a array JSON
                    $valorJson = json_encode([$valor]);
                }
            }

            if ($valorJson !== null) {
                DB::table('news')
                    ->where('id', $registro->id)
                    ->update(['destino_json' => $valorJson]);
            }
        }
    }

    private function esJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function down(): void
    {
        // Revertir: volver a string
        $driver = DB::getDriverName();

        // 1. Agregar columna temporal destino_old
        Schema::table('news', function (Blueprint $table) {
            $table->string('destino_old')->nullable()->after('destino');
        });

        // 2. Convertir JSON a string (tomar el primer elemento o usar el valor original si era string)
        $registros = DB::table('news')
            ->select('id', 'destino')
            ->whereNotNull('destino')
            ->get();

        foreach ($registros as $registro) {
            $valor = $registro->destino;
            $valorOld = null;

            if (!empty($valor)) {
                if ($this->esJson($valor)) {
                    $decodificado = json_decode($valor, true);
                    if (is_array($decodificado) && !empty($decodificado)) {
                        $valorOld = $decodificado[0];
                    } elseif (is_string($decodificado)) {
                        $valorOld = $decodificado;
                    }
                } else {
                    $valorOld = $valor;
                }
            }

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