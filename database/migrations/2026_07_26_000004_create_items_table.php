<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('talla_id')->nullable()->constrained('tallas')->nullOnDelete();
            $table->enum('tipo_seguimiento', ['cantidad', 'individual']);
            $table->string('unidad_medida')->nullable(); // unidad, caja, litro, kg...
            $table->unsignedInteger('stock_minimo')->nullable();
            $table->json('atributos')->nullable(); // datos específicos por categoría
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
