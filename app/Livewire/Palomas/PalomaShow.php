<?php

namespace App\Livewire\Palomas;

use App\Models\Paloma;
use Livewire\Component;

class PalomaShow extends Component
{
    public Paloma $paloma;

    public function mount(Paloma $paloma)
    {
        $this->authorize('view', $paloma);
        $this->paloma = $paloma;
    }

    public function render()
    {
        $this->paloma->load([
            'palomar',
            'estado',
            'padre',
            'madre',
            'historial.user',
            'vuelos' => fn($q) => $q->orderBy('vuelos.fecha', 'desc')->limit(10),
        ]);

        return view('livewire.palomas.paloma-show', [
            'paloma' => $this->paloma,
        ]);
    }
}
