<?php

namespace App\Livewire;

use App\Models\Guard;
use App\Models\News;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class EstadoNovedad extends Component
{
    #[Locked]
    public int $novedadId;

    public Guard $guardia;
    public bool $compacto = false;

    public function mount(News $novedad, Guard $guardia, bool $compacto = false): void
    {
        $this->novedadId = $novedad->id;
        $this->guardia   = $guardia;
        $this->compacto  = $compacto;
    }

    #[Computed]
    public function novedad(): ?News
    {
        // find() en vez de findOrFail(): si la novedad fue borrada
        // (p. ej. entre un poll y el siguiente), no debe romper la
        // re-hidratación del componente.
        return News::find($this->novedadId);
    }

    public function tomar(): void
    {
        if (! $this->novedad) {
            return;
        }

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
    }

    // Llamado por el polling: trae el estado actual por si otro usuario ya la tomó
    public function refrescar(): void
    {
        // La propiedad #[Computed] se invalida automáticamente al re-renderizar
    }

    // Llamado cuando novedades-guardia reabre o cierra la atención de esta novedad
    // (p. ej. la escribiente corrige la oficina), para que el badge se actualice
    // al instante sin esperar el poll
    #[On('novedad-estado-actualizado')]
    public function onEstadoActualizado(int $novedadId): void
    {
        // La propiedad #[Computed] se invalida automáticamente al re-renderizar
    }

    public function render()
    {
        return view('livewire.estado-novedad');
    }
}