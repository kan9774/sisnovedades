<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('office');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->foreignId('office_id')
                ->after('direction')
                ->constrained('oficinas');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
        });
        Schema::table('news', function (Blueprint $table) {
            $table->string('office')->nullable();
        });
    }
};