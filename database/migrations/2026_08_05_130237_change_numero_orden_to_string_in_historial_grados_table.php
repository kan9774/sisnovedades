<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * numero_orden se guarda con formato "número/año" (ej. "016/2026"),
     * no como número entero — la columna original (unsignedInteger) truncaba
     * el valor en modo estricto (SQLSTATE 1265) al intentar guardar el "/año".
     */
    public function up(): void
    {
        Schema::table('historial_grados', function (Blueprint $table) {
            $table->string('numero_orden', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('historial_grados', function (Blueprint $table) {
            $table->unsignedInteger('numero_orden')->nullable()->change();
        });
    }
};