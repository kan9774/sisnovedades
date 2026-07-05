<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vuelos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paloma_id')->constrained('palomas')->onDelete('cascade');
            $table->date('fecha');
            $table->enum('tipo', ['entrenamiento', 'competicion'])->default('entrenamiento');
            $table->decimal('distancia_km', 8, 2)->nullable();
            $table->string('punto_liberacion')->nullable();
            $table->time('hora_liberacion')->nullable();
            $table->time('hora_llegada')->nullable();
            $table->time('tiempo_vuelo')->nullable(); // calculado
            $table->decimal('velocidad_media', 8, 2)->nullable(); // calculado
            $table->integer('posicion')->nullable();
            $table->text('condiciones_climaticas')->nullable();
            $table->string('anilla_competicion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vuelos');
    }
};