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
        Schema::table('historial_grados', function (Blueprint $table) {
            $table->string('numero_orden')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * ⚠️ Rollback frágil a propósito: si numero_orden contiene valores no
     * numéricos (ej. "016/2026"), este cast a unsignedInteger va a fallar
     * en MySQL y en Postgres (SQLSTATE 22P02 "invalid input syntax").
     * No se agrega manejo de errores porque no hay una forma segura de
     * decidir automáticamente qué hacer con esos valores (truncarlos,
     * ponerlos en null, etc.) — es una decisión de negocio, no algo que
     * el rollback deba resolver solo. Si hace falta revertir esta
     * migración con datos ya "sucios", hay que limpiar/decidir el destino
     * de esos valores a mano antes de correr el rollback.
     */
    public function down(): void
    {
        Schema::table('historial_grados', function (Blueprint $table) {
            $table->unsignedInteger('numero_orden')->nullable()->change();
        });
    }
};