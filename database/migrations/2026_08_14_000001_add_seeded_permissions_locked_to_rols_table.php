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
        Schema::table('rols', function (Blueprint $table) {
            $table->boolean('seeded_permissions_locked')
                ->default(false)
                ->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rols', function (Blueprint $table) {
            $table->dropColumn('seeded_permissions_locked');
        });
    }
};
