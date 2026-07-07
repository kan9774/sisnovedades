<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->string('marca', 100)->nullable()->after('matricula');
            $table->string('modelo', 100)->nullable()->after('marca');
            $table->string('color', 50)->nullable()->after('modelo');
            $table->string('numero_chasis', 50)->nullable()->unique()->after('color');
            $table->string('numero_motor', 50)->nullable()->unique()->after('numero_chasis');
            $table->unsignedTinyInteger('ejes')->default(2)->after('numero_motor');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn([
                'marca',
                'modelo',
                'color',
                'numero_chasis',
                'numero_motor',
                'ejes',
            ]);
        });
    }
};