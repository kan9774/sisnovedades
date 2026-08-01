<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY reescribe la columna preservando los datos existentes.
        // 'active' sigue siendo el default, igual que en la tabla original.
        DB::statement("ALTER TABLE users MODIFY status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY status VARCHAR(255) NOT NULL DEFAULT 'active'");
    }
};