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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('credencial_departamento_id')
                ->nullable()
                ->after('unidad_id') // ajustar si 'unidad_id' no existe justo antes en tu tabla
                ->constrained('departamentos');
            $table->string('credencial_serie', 10)->nullable();
            $table->string('credencial_numero', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('credencial_departamento_id');
            $table->dropColumn(['credencial_serie', 'credencial_numero']);
        });
    }
};