<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_apoyo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('color', 7); // hex, ej "#28a745"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_apoyo');
    }
};
