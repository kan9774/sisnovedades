<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumen_vehiculos_diario', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('guardia_id')->constrained('guards')->onDelete('cascade');
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');
            $table->integer('total_kms')->default(0);
            $table->decimal('total_litros', 10, 2)->default(0);
            $table->integer('cantidad_salidas')->default(0);
            $table->timestamps();
            
            $table->unique(['fecha', 'vehiculo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumen_vehiculos_diario');
    }
};