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
    Schema::table('vehiculos', function (Blueprint $table) {
        // El estado puede ser un enum o un string. Usamos enum para restringir valores.
        $table->enum('estado', ['verde', 'amarillo', 'rojo', 'negro'])
              ->default('verde')
              ->after('activo'); // lo colocamos después de 'activo'
    });
}

public function down(): void
{
    Schema::table('vehiculos', function (Blueprint $table) {
        $table->dropColumn('estado');
    });
}
};
