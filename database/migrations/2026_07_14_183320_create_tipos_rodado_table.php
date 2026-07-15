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
        Schema::create('tipos_rodado', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                 // ej: "Trasera moto 90/90"
            $table->string('medida')->nullable();      // ej: "90/90-19"
            $table->enum('posicion', ['delantero', 'trasero', 'unico'])->default('unico');
            $table->string('marca')->nullable();
            $table->decimal('presion_recomendada', 5, 2)->nullable(); // PSI
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_rodado');
    }
};
