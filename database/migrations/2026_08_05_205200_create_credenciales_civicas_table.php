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
        Schema::create('credenciales_civicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('departamento_id')->constrained('departamentos');
            $table->string('serie', 10);
            $table->string('numero', 20);
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable(); // null = vigente
            $table->timestamps();

            $table->index(['user_id', 'fecha_hasta']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credenciales_civicas');
    }
};