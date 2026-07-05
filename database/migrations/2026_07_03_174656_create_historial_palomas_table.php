<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_palomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paloma_id')->constrained('palomas')->onDelete('cascade');
            $table->enum('evento', ['cambio_estado', 'venta', 'prestamo', 'retorno', 'muerte', 'ausente'])->default('cambio_estado');
            $table->foreignId('estado_anterior_id')->nullable()->constrained('estados_paloma')->onDelete('set null');
            $table->foreignId('estado_nuevo_id')->nullable()->constrained('estados_paloma')->onDelete('set null');
            $table->string('destino')->nullable(); // para venta/préstamo
            $table->date('fecha_evento');
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_palomas');
    }
};