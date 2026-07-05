<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vuelos', function (Blueprint $table) {
            $table->dropForeign(['paloma_id']); // revisa el nombre real de la FK si difiere
            $table->dropColumn([
                'paloma_id',
                'distancia_km',
                'hora_llegada',
                'tiempo_vuelo',
                'velocidad_media',
                'posicion',
                'anilla_competicion',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('vuelos', function (Blueprint $table) {
            $table->foreignId('paloma_id')->nullable()->constrained('palomas');
            $table->decimal('distancia_km', 8, 2)->nullable();
            $table->time('hora_llegada')->nullable();
            $table->time('tiempo_vuelo')->nullable();
            $table->decimal('velocidad_media', 8, 2)->nullable();
            $table->unsignedInteger('posicion')->nullable();
            $table->string('anilla_competicion', 50)->nullable();
        });
    }
};