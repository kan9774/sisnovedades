<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('tipo', ['deposito', 'oficina', 'vehiculo', 'persona']);
            // id del registro real en su tabla correspondiente (vehiculos, oficinas, users).
            // null para 'deposito' si no tiene una tabla propia asociada.
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'referencia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
