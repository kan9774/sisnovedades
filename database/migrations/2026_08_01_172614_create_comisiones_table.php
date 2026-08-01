<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidades'); // unidad donde presta servicio transitorio
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable(); // null = comisión vigente
            $table->string('tipo_orden'); // O.B., O.Bn., O.C.G.E., Otros
            $table->string('numero_orden')->nullable(); // ej: "002/2026", "Minuta N° 015"
            $table->string('motivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comisiones');
    }
};