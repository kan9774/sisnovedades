<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidades');
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable(); // null = pase vigente
            $table->string('numero_orden')->nullable(); // ej: "O.B. N° 006/2026", "Minuta N° 015/2026"
            $table->string('motivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pases');
    }
};