<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('causales_baja')->insert([
            [
                'nombre' => 'Falta a lista',
                'permite_reingreso' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Rescisión del C.S.M.',
                'permite_reingreso' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('causales_baja')
            ->whereIn('nombre', ['Falta a lista', 'Rescisión del C.S.M.'])
            ->delete();
    }
};