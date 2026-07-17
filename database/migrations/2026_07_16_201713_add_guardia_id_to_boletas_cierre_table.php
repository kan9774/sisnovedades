<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('boletas_cierre', function (Blueprint $table) {
            $table->unsignedBigInteger('guardia_id')->nullable()->after('salida_id');
            $table->foreign('guardia_id')->references('id')->on('guards')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boletas_cierre', function (Blueprint $table) {
            $table->dropForeign(['guardia_id']);
            $table->dropColumn('guardia_id');
        });
    }
};
