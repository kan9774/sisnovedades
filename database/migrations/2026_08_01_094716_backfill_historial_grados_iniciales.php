<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MARCA_BACKFILL = 'Carga inicial retroactiva (backfill historial_grados)';

    public function up(): void
    {
        User::query()
            ->whereNotNull('grado_id')
            ->whereDoesntHave('historialGrados')
            ->chunkById(200, function ($usuarios) {
                foreach ($usuarios as $user) {
                    $user->historialGrados()->create([
                        'grado_id' => $user->grado_id,
                        'tipo' => 'ascenso',
                        'numero_orden' => null,
                        'fecha_cambio' => $user->created_at?->toDateString() ?? now()->toDateString(),
                        'resolucion' => null,
                        'observaciones' => self::MARCA_BACKFILL,
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('historial_grados')
            ->where('observaciones', self::MARCA_BACKFILL)
            ->delete();
    }
};