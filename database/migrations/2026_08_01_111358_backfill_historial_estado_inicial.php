<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MARCA_BACKFILL = 'Carga inicial retroactiva (backfill historial_estado)';

    public function up(): void
    {
        User::query()
            ->where('is_super_admin', false)
            ->whereDoesntHave('historialEstados')
            ->chunkById(200, function ($usuarios) {
                foreach ($usuarios as $user) {
                    // create() directo (no historialEstados()->create()) para
                    // saltear el hook `created()` que sincroniza users.status:
                    // acá el usuario ya tiene el status que tiene que tener,
                    // no queremos que el backfill se lo pise.
                    \App\Models\HistorialEstado::withoutEvents(function () use ($user) {
                        $user->historialEstados()->create([
                            'tipo' => 'alta',
                            'fecha' => $user->created_at?->toDateString() ?? now()->toDateString(),
                            'motivo' => self::MARCA_BACKFILL,
                        ]);
                    });
                }
            });
    }

    public function down(): void
    {
        DB::table('historial_estado')
            ->where('motivo', self::MARCA_BACKFILL)
            ->delete();
    }
};