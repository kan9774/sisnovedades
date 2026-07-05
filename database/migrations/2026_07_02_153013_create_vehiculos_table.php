<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('matricula', 20)->unique();
            $table->enum('tipo_combustible', ['gas_oil', 'nafta']);
            $table->decimal('consumo_litros_por_km', 10, 4)->nullable();
            $table->boolean('sin_cuentakilometros')->default(false);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};