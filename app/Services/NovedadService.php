<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NovedadService
{
    /**
     * Marcar novedad pendiente como "visto" y asignarla al usuario actual.
     * También cierra las notificaciones asociadas para el resto de la oficina.
     */
    public static function tomarSiPendiente(?News $novedad): void
    {
        if (!$novedad || $novedad->estado_atencion !== 'pendiente') {
            return;
        }

        $novedad->estado_atencion = 'visto';
        $novedad->tomado_por_id = Auth::id();
        $novedad->tomado_en = now();
        $novedad->save();

        DatabaseNotification::where('data->novedad_id', $novedad->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Marcar notificación como leída y procesar la novedad asociada.
     * Devuelve [novedadId, guardiaId] para que el caller decida si redirige.
     */
    public static function marcarLeida(DatabaseNotification $notificacion): array
    {
        $notificacion->markAsRead();

        $novedadId = $notificacion->data['novedad_id'] ?? null;
        $guardiaId = $notificacion->data['guardia_id'] ?? null;

        if ($novedadId) {
            self::tomarSiPendiente(News::find($novedadId));
        }

        return [$novedadId, $guardiaId];
    }
}
