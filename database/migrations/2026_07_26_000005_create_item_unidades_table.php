<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_unidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('numero_serie')->nullable();
            $table->enum('estado', ['disponible', 'asignado', 'en_reparacion', 'baja'])
                ->default('disponible');
            $table->foreignId('ubicacion_actual_id')
                ->nullable()
                ->constrained('ubicaciones')
                ->nullOnDelete();
            $table->foreignId('responsable_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('fecha_alta')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_unidades');
    }
};
