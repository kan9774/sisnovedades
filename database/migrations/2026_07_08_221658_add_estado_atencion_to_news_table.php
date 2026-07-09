<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // null = no aplica (novedad no urgente) | 'pendiente' | 'visto'
            $table->string('estado_atencion')->nullable()->after('confirmed_at');
            $table->foreignId('tomado_por_id')->nullable()->after('estado_atencion')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('tomado_en')->nullable()->after('tomado_por_id');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tomado_por_id');
            $table->dropColumn(['estado_atencion', 'tomado_en']);
        });
    }
};