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
        Schema::table('novedades_rancho', function (Blueprint $table) {
            $table->unsignedInteger('colacion')->nullable()->after('desayuno');
        });

        Schema::table('rancho_menus', function (Blueprint $table) {
            $table->string('menu_colacion')->nullable()->after('menu_desayuno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('novedades_rancho', function (Blueprint $table) {
            $table->dropColumn('colacion');
        });

        Schema::table('rancho_menus', function (Blueprint $table) {
            $table->dropColumn('menu_colacion');
        });
    }
};