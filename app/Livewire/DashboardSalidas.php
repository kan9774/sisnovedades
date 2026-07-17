<?php declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use App\Models\SalidaVehiculo;

class DashboardSalidas extends Component
{
    public $ultimasSalidas;

    public function mount()
    {
        $this->loadSalidas();
    }

    public function loadSalidas()
    {
        $this->ultimasSalidas = SalidaVehiculo::with(['vehiculo', 'conductor', 'guardia'])
            ->latest('created_at')
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard-salidas');
    }
}
