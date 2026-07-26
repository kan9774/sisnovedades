<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('item_unidad_id')
                ->nullable()
                ->constrained('item_unidades')
                ->nullOnDelete();
            $table->enum('tipo', ['entrada', 'salida', 'transferencia', 'ajuste', 'baja']);
            $table->integer('cantidad')->nullable(); // null cuando es item_unidad individual
            $table->foreignId('ubicacion_origen_id')
                ->nullable()
                ->constrained('ubicaciones')
                ->nullOnDelete();
            $table->foreignId('ubicacion_destino_id')
                ->nullable()
                ->constrained('ubicaciones')
                ->nullOnDelete();
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('motivo')->nullable();
            $table->string('referencia')->nullable(); // ej: id de novedad relacionada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
