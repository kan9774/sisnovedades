<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'sqlite') {
            return; // SQLite no soporta dropColumn, la columna ya existe
        }

        Schema::table('palomas', function (Blueprint $table) {
            $table->enum('estado_sanitario', ['Bien', 'Enferma'])->default('Bien')->after('estado_id');
        });
    }

    public function down()
    {
        Schema::table('palomas', function (Blueprint $table) {
            $table->dropColumn('estado_sanitario');
        });
    }
};
