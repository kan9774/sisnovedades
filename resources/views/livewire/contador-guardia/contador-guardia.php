<?php

use App\Models\Guard;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Guard $guardia;

    /** 'novedades' | 'salidas' | 'personal' */
    public string $tipo;

    #[Computed]
    public function total(): int
    {
        return match ($this->tipo) {
            'novedades' => $this->guardia->novedades()->count(),
            'salidas'   => $this->guardia->salidasVehiculos()->count(),
            'personal'  => $this->guardia->novedadesPersonal()->count(),
            default     => 0,
        };
    }

    #[On('guardia-contador-actualizado')]
    public function refrescar(string $tipo, int $guardiaId): void
    {
        if ($tipo === $this->tipo && $guardiaId === $this->guardia->id) {
            unset($this->total);
        }
    }

    public function render()
    {
        return view('livewire.contador-guardia.contador-guardia');
    }
};