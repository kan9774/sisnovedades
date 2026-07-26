<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->constrained('ubicaciones')->cascadeOnDelete();
            $table->integer('cantidad')->default(0);
            $table->timestamps();

            $table->unique(['item_id', 'ubicacion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
