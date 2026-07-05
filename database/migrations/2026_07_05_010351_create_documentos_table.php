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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_documento_id')->constrained('categorias_documentos');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('archivo_path');       // ruta en storage
            $table->string('nombre_original');    // nombre original del archivo subido
            $table->string('extension', 10);      // pdf, docx
            $table->unsignedBigInteger('tamanio'); // bytes
            $table->foreignId('subido_por')->constrained('users');
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
