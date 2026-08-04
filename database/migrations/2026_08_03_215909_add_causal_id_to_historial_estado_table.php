<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_estado', function (Blueprint $table) {
            $table->foreignId('causal_id')
                ->nullable()
                ->after('motivo')
                ->constrained('causales_baja');
        });
    }

    public function down(): void
    {
        Schema::table('historial_estado', function (Blueprint $table) {
            $table->dropConstrainedForeignId('causal_id');
        });
    }
};