<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign('documentos_subido_por_foreign');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->unsignedBigInteger('subido_por')->nullable()->change();

            $table->foreign('subido_por')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign('documentos_subido_por_foreign');
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->foreign('subido_por')
                ->references('id')->on('users');
        });
    }
};