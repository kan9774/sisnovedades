<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            // Nullable: los movimientos sueltos (entradas, ajustes,
            // devoluciones de un solo ítem desde Movimientos) no
            // pertenecen a ninguna entrega agrupada.
            $table->foreignId('entrega_id')
                ->nullable()
                ->after('item_unidad_id')
                ->constrained('entregas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entrega_id');
        });
    }
};