<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimientos_vehiculo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->cascadeOnDelete();
            $table->enum('tipo', ['preventivo', 'correctivo', 'revision_tecnica', 'otro'])->default('preventivo');
            $table->date('fecha');
            $table->unsignedInteger('kilometraje')->nullable();
            $table->string('descripcion', 500);
            $table->decimal('costo', 10, 2)->nullable();
            $table->string('taller', 150)->nullable();
            $table->date('proximo_mantenimiento_fecha')->nullable();
            $table->unsignedInteger('proximo_mantenimiento_km')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos_vehiculo');
    }
};