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
    Schema::create('direcciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->enum('tipo', ['particular', 'laboral', 'otro'])->default('particular');
        $table->foreignId('departamento_id')->constrained('departamentos');
        $table->string('localidad')->nullable();
        $table->string('calle');
        $table->string('numero')->nullable();
        $table->string('esquina')->nullable();
        $table->string('apartamento')->nullable();
        $table->string('barrio')->nullable();
        $table->string('codigo_postal', 10)->nullable();
        $table->string('referencia')->nullable();
        $table->boolean('es_principal')->default(false);
        $table->timestamps();
        $table->softDeletes();

        $table->index(['user_id', 'tipo']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('direcciones');
    }
};
