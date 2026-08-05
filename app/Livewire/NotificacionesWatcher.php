<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\News;

class NotificacionesWatcher extends Component
{
    public ?string $ultimoTimestamp = null;

    public function mount(): void
    {
        // Arranca el reloj "ahora": todo lo creado después de este instante
        // se considera nuevo para efectos de notificación.
        $this->ultimoTimestamp = now()->toDateTimeString();
    }

    public function poll(): void
    {
        $oficinaId = auth()->user()->oficina_id;

        if (! $oficinaId) {
            return;
        }

        $nuevas = News::query()
            ->where('created_at', '>', $this->ultimoTimestamp)
            ->where('user_id', '!=', auth()->id()) // no notificarte tus propias novedades
            ->where('direction', 'Recibido')
            ->where('estado_atencion', 'pendiente')
            ->where('office_id', $oficinaId)
            ->whereHas('guardia', fn ($q) => $q->where('status', 'open'))
            ->latest()
            ->get();

        if ($nuevas->isEmpty()) {
            return;
        }

        foreach ($nuevas as $noticia) {
            $this->dispatch('nueva-novedad',
                titulo: 'Nueva novedad de guardia',
                cuerpo: str($noticia->affair ?: $noticia->text ?? 'Se registró una nueva novedad')->limit(120)->toString(),
                url: route('admin.guardias.novedades.show', [
                    'guardia' => $noticia->guard_id,
                    'novedad' => $noticia->id,
                ]),
            );
        }

        $this->ultimoTimestamp = now()->toDateTimeString();
    }

    public function render()
    {
        return view('livewire.notificaciones-watcher');
    }
}