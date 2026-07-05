<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardia_escribientes', function (Blueprint $table) {
            $table->foreignId('guardia_id')->constrained('guards')->onDelete('cascade');
            $table->foreignId('escribiente_id')->constrained('users')->onDelete('cascade');
            $table->primary(['guardia_id', 'escribiente_id']);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardia_escribientes');
    }
};