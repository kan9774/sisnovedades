<?php

namespace App\Observers;

use App\Models\News;
use App\Models\User;
use App\Notifications\NovedadUrgenteNotification;

class UserObserver
{
    public function created(User $user): void
    {
        $this->notificarPendientes($user);
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('oficina_id')) {
            $this->notificarPendientes($user);
        }
    }

    /**
     * Genera notificaciones por las novedades pendientes que ya existan
     * en la oficina del usuario (nuevo o recién reasignado), para que
     * la campanita quede al día sin depender de cuándo se creó cada novedad.
     */
private function notificarPendientes(User $user): void
{
    if (!$user->oficina_id) {
        return;
    }

    News::pendientes()
        ->deGuardiaAbierta()
        ->recientes()
        ->where('direction', 'Recibido') // ← agregar
        ->where('office_id', $user->oficina_id)
        ->get()
        ->each(function (News $novedad) use ($user) {
            $yaNotificado = $user->notifications()
                ->where('data->novedad_id', $novedad->id)
                ->exists();

            if (!$yaNotificado) {
                $user->notify(new NovedadUrgenteNotification($novedad));
            }
        });
}
}
