<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega con_adjuntos y con_zip a guardia_correos_fallidos y
     * guardia_correos_enviados para persistir el modo de envío y
     * permitir reintentos fieles al original.
     */
    public function up(): void
    {
        Schema::table('guardia_correos_fallidos', function (Blueprint $table) {
            $table->boolean('con_adjuntos')->default(false)->after('motivo');
            $table->boolean('con_zip')->default(false)->after('con_adjuntos');
        });

        Schema::table('guardia_correos_enviados', function (Blueprint $table) {
            $table->boolean('con_adjuntos')->default(false)->after('message_id');
            $table->boolean('con_zip')->default(false)->after('con_adjuntos');
        });

        // Backfill: los fallos existentes se marcan sin adjuntos/ZIP (false)
        DB::table('guardia_correos_fallidos')->update([
            'con_adjuntos' => false,
            'con_zip'      => false,
        ]);
    }

    public function down(): void
    {
        Schema::table('guardia_correos_fallidos', function (Blueprint $table) {
            $table->dropColumn(['con_adjuntos', 'con_zip']);
        });

        Schema::table('guardia_correos_enviados', function (Blueprint $table) {
            $table->dropColumn(['con_adjuntos', 'con_zip']);
        });
    }
};
