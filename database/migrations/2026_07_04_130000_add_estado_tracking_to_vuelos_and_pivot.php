<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vuelos', function (Blueprint $table) {
            $table->enum('estado', ['en_curso', 'finalizado'])->default('en_curso')->after('observaciones');
        });

        Schema::table('paloma_vuelo', function (Blueprint $table) {
            $table->foreignId('estado_anterior_id')->nullable()->after('vuelo_id')
                ->constrained('estados_paloma')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vuelos', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        Schema::table('paloma_vuelo', function (Blueprint $table) {
            $table->dropForeign(['estado_anterior_id']);
            $table->dropColumn('estado_anterior_id');
        });
    }
};