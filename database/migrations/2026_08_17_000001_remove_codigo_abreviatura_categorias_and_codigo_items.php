<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina la abreviatura de categoría y el código de ítem.
     * Ambos campos ya no se usan: la abreviatura generaba códigos
     * automáticos de ítems, funcionalidad que se elimina por completo.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return; // SQLite no soporta dropColumn con constraints
        }

        // 1) Quitar unique constraint de codigo_abreviatura (se aplica al dropColumn)
        // 2) Quitar columna codigo_abreviatura de categorias
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('codigo_abreviatura');
        });

        // 3) Quitar columna codigo de items (unique implícito en dropColumn)
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });
    }

    public function down(): void
    {
        // Re-agregar codigo_abreviatura a categorias
        Schema::table('categorias', function (Blueprint $table) {
            $table->string('codigo_abreviatura', 6)->nullable()->unique()->after('slug');
        });

        // Re-agregar codigo a items (con unique)
        Schema::table('items', function (Blueprint $table) {
            $table->string('codigo')->unique()->after('id');
        });
    }
};
