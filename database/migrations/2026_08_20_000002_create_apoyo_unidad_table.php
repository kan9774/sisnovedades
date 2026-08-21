<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apoyo_unidad', function (Blueprint $table) {
            $table->foreignId('apoyo_id')->constrained('apoyos')->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['apoyo_id', 'unidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apoyo_unidad');
    }
};
