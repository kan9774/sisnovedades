<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paloma_vuelo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paloma_id')->constrained('palomas')->cascadeOnDelete();
            $table->foreignId('vuelo_id')->constrained('vuelos')->cascadeOnDelete();
            $table->decimal('distancia_km', 8, 2)->nullable();
            $table->time('hora_llegada')->nullable();
            $table->time('tiempo_vuelo')->nullable();
            $table->decimal('velocidad_media', 8, 2)->nullable();
            $table->unsignedInteger('posicion')->nullable();
            $table->string('anilla_competicion', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['paloma_id', 'vuelo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paloma_vuelo');
    }
};