<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Nulo = el ítem no tiene vencimiento (ej: un escritorio).
            // Con valor = cada lote/unidad vence a los N meses de recibido.
            $table->unsignedInteger('vida_util_meses')->nullable()->after('unidad_medida');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('vida_util_meses');
        });
    }
};