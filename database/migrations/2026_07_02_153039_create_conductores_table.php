<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conductores', function (Blueprint $table) {
            $table->id();
            
            // Datos personales
            $table->string('grado', 50);
            $table->string('primer_nombre', 100);
            $table->string('segundo_nombre', 100)->nullable();
            $table->string('primer_apellido', 100);
            $table->string('segundo_apellido', 100)->nullable();
            $table->string('documento', 20)->unique();
            
            // Licencia
            $table->string('nro_licencia', 50);
            $table->string('categoria_licencia', 10);
            $table->date('fecha_vencimiento_licencia');
            
            // Carné salud
            $table->string('lugar_carne_salud', 255)->nullable();
            $table->date('fecha_vencimiento_carne_salud')->nullable();
            
            // Carné habilitante
            $table->string('lugar_carne_habilitante', 255)->nullable();
            $table->date('fecha_vencimiento_carne_habilitante')->nullable();
            $table->string('tipo_vehiculo_habilitado', 100)->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conductores');
    }
};
