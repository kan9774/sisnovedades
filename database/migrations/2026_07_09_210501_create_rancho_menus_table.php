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
        Schema::create('rancho_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guard_id')->unique()->constrained('guards')->cascadeOnDelete();
            $table->string('menu_desayuno')->nullable();
            $table->string('menu_almuerzo')->nullable();
            $table->string('menu_merienda')->nullable();
            $table->string('menu_cena')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rancho_menus');
    }
};
