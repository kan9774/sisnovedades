<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL permite múltiples NULL en una columna UNIQUE (email), así
        // que sacar el NOT NULL no rompe el índice único existente.
        // (Postgres se comporta igual: NULLs no chocan entre sí en un índice único.)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN name DROP NOT NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN last_name DROP NOT NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN email DROP NOT NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN password DROP NOT NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite: columnas ya son flexibles (sin NOT NULL estricto).
        } else {
            DB::statement("ALTER TABLE users MODIFY name VARCHAR(255) NULL");
            DB::statement("ALTER TABLE users MODIFY last_name VARCHAR(255) NULL");
            DB::statement("ALTER TABLE users MODIFY email VARCHAR(255) NULL");
            DB::statement("ALTER TABLE users MODIFY password VARCHAR(255) NULL");
        }

        Schema::table('users', function (Blueprint $table) {
            // null = todavía a mitad del wizard (solo tiene C.I. y quizás
            // algún paso más cargado). Se completa en el último paso.
            $table->timestamp('perfil_completo_at')->nullable()->after('ci_dv');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('perfil_completo_at');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN name SET NOT NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN last_name SET NOT NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN email SET NOT NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN password SET NOT NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite: no hay NOT NULL que restaurar, columnas ya flexibles.
        } else {
            DB::statement("ALTER TABLE users MODIFY name VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE users MODIFY last_name VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL");
        }
    }
};