<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reescribe la columna preservando los datos existentes.
        // 'active' sigue siendo el default, igual que en la tabla original.
        // El check de valores permitidos ('active'/'inactive') queda a nivel
        // de validación en el modelo/form request, ya que un ENUM real no es
        // portable entre MySQL y Postgres.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ALTER COLUMN status SET DEFAULT 'active'");
            DB::statement("ALTER TABLE users ALTER COLUMN status SET NOT NULL");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active', 'inactive'))");
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite: no soporta MODIFY ni ENUM. La columna ya es TEXT con
            // DEFAULT 'active' desde la migración original, y la validación
            // de valores permitidos se maneja en el modelo/form request.
        } else {
            // MySQL / MariaDB: mantiene el ENUM real como antes.
            DB::statement("ALTER TABLE users MODIFY status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check");
            Schema::table('users', function (Blueprint $table) {
                $table->string('status')->default('active')->change();
            });
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite: no hay ENUM que revertir, columna ya es TEXT.
        } else {
            DB::statement("ALTER TABLE users MODIFY status VARCHAR(255) NOT NULL DEFAULT 'active'");
        }
    }
};