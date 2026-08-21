<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidad_modulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->string('modulo');
            $table->timestamps();

            $table->unique(['unidad_id', 'modulo']);
            $table->index('modulo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidad_modulo');
    }
};
