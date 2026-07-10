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
        Schema::create('novedades_rancho', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guard_id')->constrained('guards')->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidades');
            $table->unsignedInteger('desayuno')->nullable();
            $table->unsignedInteger('almuerzo')->nullable();
            $table->unsignedInteger('merienda')->nullable();
            $table->unsignedInteger('cena')->nullable();
            $table->text('menu')->nullable();
            $table->timestamps();

            $table->unique(['guard_id', 'unidad_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novedades_ranchos');
    }
};
