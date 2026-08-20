<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardia_correos_fallidos', function (Blueprint $table) {
            $table->string('tipo')->default('inmediato');
            $table->string('message_id')->nullable();
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::table('guardia_correos_fallidos', function (Blueprint $table) {
            $table->dropIndex(['message_id']);
            $table->dropColumn(['tipo', 'message_id']);
        });
    }
};
