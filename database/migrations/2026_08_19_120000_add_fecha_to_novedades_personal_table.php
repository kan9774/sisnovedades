<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega columna `fecha` (date, nullable) para registrar la fecha
     * explícita de cada novedad de personal, independiente de la fecha
     * de la guardia. Backfill con la fecha de la guardia asociada.
     */
    public function up(): void
    {
        Schema::table('novedades_personal', function (Blueprint $table) {
            $table->date('fecha')->nullable()->after('hora');
        });

        // Backfill: todas las novedades existentes heredan la fecha de su guardia
        DB::statement(
            "UPDATE novedades_personal SET fecha = (SELECT guards.date FROM guards WHERE guards.id = novedades_personal.guard_id) WHERE fecha IS NULL"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('novedades_personal', function (Blueprint $table) {
            $table->dropColumn('fecha');
        });
    }
};
