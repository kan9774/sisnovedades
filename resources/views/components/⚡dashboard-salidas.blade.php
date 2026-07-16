<?php

use Livewire\Component;
use App\Models\SalidaVehiculo;

new class extends Component
{
    public $ultimasSalidas;

    public function mount()
    {
        $this->loadSalidas();
    }

    public function loadSalidas()
    {
        $this->ultimasSalidas = SalidaVehiculo::with(['vehiculo', 'conductor'])
            ->latest()
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard-salidas');
    }
};
?>

<div>
    {{-- content --}}
</div>
