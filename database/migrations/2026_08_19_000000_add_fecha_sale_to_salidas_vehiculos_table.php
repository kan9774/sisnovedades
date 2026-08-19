<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salidas_vehiculos', function (Blueprint $table) {
            $table->date('fecha_sale')->nullable()->after('hora_sale');
        });

        DB::statement("
            UPDATE salidas_vehiculos
            SET fecha_sale = (SELECT guards.date FROM guards WHERE guards.id = salidas_vehiculos.guardia_id)
            WHERE fecha_sale IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('salidas_vehiculos', function (Blueprint $table) {
            $table->dropColumn('fecha_sale');
        });
    }
};
