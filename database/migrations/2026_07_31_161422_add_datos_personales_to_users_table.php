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
        Schema::table('users', function (Blueprint $table) {
            $table->string('segundo_nombre')->nullable()->after('name');
            $table->string('segundo_apellido')->nullable()->after('last_name');
            $table->date('fecha_nacimiento')->nullable()->after('segundo_apellido');
            $table->char('ci', 7)->nullable()->after('fecha_nacimiento');
            $table->char('ci_dv', 1)->nullable()->after('ci');

            $table->unique('ci');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['ci']);
            $table->dropColumn([
                'segundo_nombre',
                'segundo_apellido',
                'fecha_nacimiento',
                'ci',
                'ci_dv',
            ]);
        });
    }
};
