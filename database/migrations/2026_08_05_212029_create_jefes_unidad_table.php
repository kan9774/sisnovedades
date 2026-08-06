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
        Schema::create('jefes_unidad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->foreignId('grado_id')->constrained('grados');
            $table->string('cargo')->default('Jefe del Batallón de Comunicaciones N° 1'); // ajustar texto exacto si corresponde
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable(); // null = vigente
            $table->timestamps();

            $table->index('fecha_hasta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jefes_unidad');
    }
};