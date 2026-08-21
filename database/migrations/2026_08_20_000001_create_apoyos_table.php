<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apoyos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_id')->constrained('tipos_apoyo')->restrictOnDelete();
            $table->foreignId('organismo_id')->constrained('organismos')->restrictOnDelete();
            $table->foreignId('documento_novedad_id')->nullable()->constrained('news')->nullOnDelete();
            $table->string('documento_texto')->nullable();
            $table->dateTime('desde');
            $table->dateTime('hasta');
            $table->foreignId('por_documento_novedad_id')->nullable()->constrained('news')->nullOnDelete();
            $table->string('por_documento_texto')->nullable();
            $table->string('estado')->default('pendiente'); // pendiente, activo, cumplido, suspendido, sin_efecto
            $table->foreignId('cumplido_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('cumplido_at')->nullable();
            $table->foreignId('registrado_por_id')->constrained('users')->restrictOnDelete();
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apoyos');
    }
};
