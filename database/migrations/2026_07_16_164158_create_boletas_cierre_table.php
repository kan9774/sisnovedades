<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('boletas_cierre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salida_id')->constrained('salidas_vehiculos')->onDelete('cascade');
            $table->date('fecha_entra');
            $table->time('hora_entra');
            $table->integer('kms_entra')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletas_cierre');
    }
};
