<?php

use App\Models\Pase;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MARCA_BACKFILL = 'Carga inicial retroactiva (backfill pases)';

    public function up(): void
    {
        User::query()
            ->where('is_super_admin', false)
            ->whereNotNull('unidad_id')
            ->whereDoesntHave('pases')
            ->chunkById(200, function ($usuarios) {
                foreach ($usuarios as $user) {
                    // create() directo del modelo, sin pasar por
                    // fechaDesdeParaPase(): acá NO queremos la transformación
                    // "mes siguiente" que aplica a un pase nuevo, sino usar
                    // la fecha real de ingreso tal cual está.
                    //
                    // withoutEvents() evita disparar los hooks creating()/created()
                    // de Pase (cierre automático de pase anterior + validación de
                    // comisión vigente): en este backfill no hay pase anterior que
                    // cerrar, y la tabla `comisiones` todavía no existe en este punto
                    // de la secuencia de migraciones.
                    Pase::withoutEvents(function () use ($user) {
                        $user->pases()->create([
                            'unidad_id' => $user->unidad_id,
                            'fecha_desde' => $user->created_at?->toDateString() ?? now()->toDateString(),
                            'numero_orden' => null,
                            'motivo' => self::MARCA_BACKFILL,
                        ]);
                    });
                }
            });
    }

    public function down(): void
    {
        DB::table('pases')
            ->where('motivo', self::MARCA_BACKFILL)
            ->delete();
    }
};