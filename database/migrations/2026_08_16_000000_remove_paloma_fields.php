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
            return; // SQLite no soporta dropColumn con FK constraints
        }

        Schema::table('palomas', function (Blueprint $table) {
            $table->dropForeign(['padre_id']);
            $table->dropForeign(['madre_id']);
            $table->dropColumn(['nombre', 'color', 'raza', 'origen', 'padre_id', 'madre_id', 'estado_sanitario']);
        });
    }

    public function down()
    {
        Schema::table('palomas', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('anilla');
            $table->string('color')->nullable()->after('sexo');
            $table->string('raza')->nullable()->after('color');
            $table->string('origen')->nullable()->after('raza');
            $table->foreignId('padre_id')->nullable()->after('origen')->constrained('palomas')->onDelete('set null');
            $table->foreignId('madre_id')->nullable()->after('padre_id')->constrained('palomas')->onDelete('set null');
            $table->enum('estado_sanitario', ['Bien', 'Enferma'])->default('Bien')->after('estado_id');
        });
    }
};
