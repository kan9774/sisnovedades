<?php

namespace App\Livewire;

use App\Models\Guard;
use App\Models\News;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class EstadoNovedad extends Component
{
    public News $novedad;
    public Guard $guardia;
    public bool $compacto = false;

    public function mount(News $novedad, Guard $guardia, bool $compacto = false): void
    {
        $this->novedad  = $novedad;
        $this->guardia  = $guardia;
        $this->compacto = $compacto;
    }

    public function tomar(): void
    {
        $this->authorize('tomar', $this->novedad);

        if ($this->novedad->estado_atencion !== 'pendiente') {
            return;
        }

        $this->novedad->update([
            'estado_atencion' => 'visto',
            'tomado_por_id'   => Auth::id(),
            'tomado_en'       => now(),
        ]);

        DatabaseNotification::where('data->novedad_id', $this->novedad->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->novedad->refresh();
    }

    // Llamado por el polling: trae el estado actual por si otro usuario ya la tomó
    public function refrescar(): void
    {
        $this->novedad->refresh();
    }

    // Llamado cuando novedades-guardia reabre o cierra la atención de esta novedad
    // (p. ej. la escribiente corrige la oficina), para que el badge se actualice
    // al instante sin esperar el poll
    #[On('novedad-estado-actualizado')]
    public function onEstadoActualizado(int $novedadId): void
    {
        if ($novedadId === $this->novedad->id) {
            $this->novedad->refresh();
        }
    }

    public function render()
    {
        return view('livewire.estado-novedad');
    }
}