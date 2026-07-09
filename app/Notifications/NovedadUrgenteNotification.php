<?php

namespace App\Notifications;

use App\Models\News;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NovedadUrgenteNotification extends Notification
{
    use Queueable;

    public function __construct(public News $novedad) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'novedad_id'    => $this->novedad->id,
            'guardia_id'    => $this->novedad->guard_id,
            'tipo'          => $this->novedad->type,
            'clasification' => $this->novedad->clasification,
            'number'        => $this->novedad->number,
            'oficina'       => $this->novedad->oficina->nombre ?? null,
            'mensaje'       => "Novedad {$this->novedad->type} N° {$this->novedad->number} ({$this->novedad->clasification}) pendiente de atención.",
        ];
    }
}
