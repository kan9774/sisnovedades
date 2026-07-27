<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->constrained('ubicaciones')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();

            // Fecha desde la que empieza a correr la vida útil de este lote.
            $table->date('fecha_recibido');

            // cantidad_inicial queda fija como referencia histórica del lote;
            // cantidad_actual se va descontando por FEFO en salidas/transferencias.
            $table->unsignedInteger('cantidad_inicial');
            $table->unsignedInteger('cantidad_actual');

            $table->string('referencia')->nullable();
            $table->timestamps();

            // Consulta típica de FEFO: lotes de un item+ubicación con
            // stock disponible, ordenados por los que vencen antes.
            $table->index(['item_id', 'ubicacion_id', 'fecha_recibido']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes_stock');
    }
};