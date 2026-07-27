<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();

            // 'entrega': depósito -> persona/oficina/vehículo
            // 'devolucion': persona/oficina/vehículo -> depósito
            $table->enum('tipo', ['entrega', 'devolucion']);

            $table->foreignId('ubicacion_origen_id')->constrained('ubicaciones');
            $table->foreignId('ubicacion_destino_id')->constrained('ubicaciones');
            $table->foreignId('usuario_id')->constrained('users');
            $table->text('motivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};