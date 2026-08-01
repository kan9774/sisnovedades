<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial de cambios de grado de cada usuario: tanto ascensos como
     * degradaciones. Cada fila es un cambio (o el grado de ingreso).
     * El grado "vigente" de un usuario es el de la fila con fecha_cambio
     * más reciente para ese user_id, sin importar si el grado_id de esa
     * fila es numéricamente mayor o menor que el de la fila anterior.
     *
     * User.grado_id sigue existiendo como caché del grado vigente, para
     * no romper las consultas/vistas que ya lo usan directamente; esta
     * tabla es la fuente de verdad del historial completo.
     */
    public function up(): void
    {
        Schema::create('historial_grados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('grado_id')->constrained('grados')->restrictOnDelete();
            $table->enum('tipo', ['ascenso', 'degradacion'])->default('ascenso');
            $table->unsignedInteger('numero_orden')->nullable();
            $table->date('fecha_cambio');
            $table->string('resolucion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'fecha_cambio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_grados');
    }
};