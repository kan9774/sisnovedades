<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Los usuarios creados ANTES del wizard nunca pasaron por el Paso 3,
     * así que perfil_completo_at les quedó en null aunque ya tengan
     * name/email/password cargados. Sin este backfill, el nuevo filtro
     * de "usuarios incompletos" del listado los mostraría a todos como
     * abandonados a mitad de wizard.
     *
     * Criterio: si ya tiene name Y email cargados, se considera un
     * perfil completo de origen. Se usa created_at como fecha
     * aproximada, igual que en los otros backfills de este proyecto.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('perfil_completo_at')
            ->whereNotNull('name')
            ->whereNotNull('email')
            ->update(['perfil_completo_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        // Reversible: no hay forma de saber cuáles se pusieron por este
        // backfill vs por el wizard real, así que no se deshace nada.
        // (mismo criterio que los backfills anteriores del proyecto)
    }
};