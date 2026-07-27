<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_unidades', function (Blueprint $table) {
            $table->foreignId('proveedor_id')
                ->nullable()
                ->after('item_id')
                ->constrained('proveedores')
                ->nullOnDelete();

            // Fecha en que se recibió del proveedor; a partir de acá
            // empieza a correr la vida útil del ítem (si la tiene).
            $table->date('fecha_recibido')->nullable()->after('numero_serie');
        });
    }

    public function down(): void
    {
        Schema::table('item_unidades', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proveedor_id');
            $table->dropColumn('fecha_recibido');
        });
    }
};