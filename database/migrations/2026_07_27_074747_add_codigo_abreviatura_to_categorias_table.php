<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            // Prefijo corto para generar códigos de ítem automáticos
            // (ej: "EQC" -> EQC-0001, EQC-0002...). Editable, único,
            // se autogenera a partir del nombre si se deja vacío.
            $table->string('codigo_abreviatura', 6)->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('codigo_abreviatura');
        });
    }
};