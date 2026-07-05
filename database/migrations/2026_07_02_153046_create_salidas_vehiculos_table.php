<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salidas_vehiculos', function (Blueprint $table) {
            $table->id();

            // Relación directa con la guardia (sin pasar por novedad)
            $table->foreignId('guardia_id')
                  ->constrained('guards')
                  ->onDelete('cascade');

            $table->foreignId('vehiculo_id')
                  ->constrained('vehiculos')
                  ->onDelete('restrict');

            $table->foreignId('conductor_id')
                  ->constrained('conductores')
                  ->onDelete('restrict');

            $table->enum('tipo_combustible', ['gas_oil', 'nafta']);
            $table->time('hora_sale');
            $table->time('hora_entra')->nullable();
            $table->integer('kms_sale')->nullable();
            $table->integer('kms_entra')->nullable();
            $table->integer('kms_recorridos')->nullable();
            $table->decimal('litros', 10, 2)->nullable();
            $table->decimal('consumo_usado', 10, 4)->nullable();
            $table->text('comision');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salidas_vehiculos');
    }
};