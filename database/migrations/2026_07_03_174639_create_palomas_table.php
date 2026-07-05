<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('palomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('palomar_id')->constrained('palomares')->onDelete('cascade');
            $table->string('anilla')->unique();
            $table->string('nombre')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['macho', 'hembra', 'desconocido'])->default('desconocido');
            $table->string('color')->nullable();
            $table->string('raza')->nullable();
            $table->string('origen')->nullable();
            $table->foreignId('padre_id')->nullable()->constrained('palomas')->onDelete('set null');
            $table->foreignId('madre_id')->nullable()->constrained('palomas')->onDelete('set null');
            $table->foreignId('estado_id')->constrained('estados_paloma')->onDelete('restrict');
            $table->enum('estado_sanitario', ['Bien', 'Enferma'])->default('Bien');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('palomas');
    }
};