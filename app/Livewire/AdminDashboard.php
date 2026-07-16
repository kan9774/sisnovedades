<?php declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use App\Models\Guard;
use App\Models\Conductor;
use App\Models\SalidaVehiculo;
use App\Models\Vuelo;

class AdminDashboard extends Component
{
    public $guardiaHoy;
    public $vehiculosEnRuta;
    public $totalConductores;
    public $vuelosActivos;
    public $conductoresAlertas;
    public $ultimosVuelos;

    public function mount()
    {
        $this->actualizarDatos();
    }

    public function actualizarDatos()
    {
        // 1. Guardia del día
        $this->guardiaHoy = Guard::with(['capitan', 'oficial'])
            ->where('date', date('Y-m-d'))
            ->first();

        // 2. Conteos y Estadísticas del Box Superior
        $this->vehiculosEnRuta = SalidaVehiculo::whereNull('hora_entra')
            ->whereHas('guardia', function($q) {
                $q->where('status', 'open');
            })->count();

        $this->totalConductores = Conductor::where('activo', true)->count();
        
        // Vuelos activos (vuelos registrados en el día)
        $this->vuelosActivos = Vuelo::where('fecha', date('Y-m-d'))->count(); 

        // 3. Alertas de documentación de conductores (Vencen en los próximos 15 días o ya vencidos)
        $this->conductoresAlertas = Conductor::where('activo', true)
            ->where(function($q) {
                $q->where('fecha_vencimiento_licencia', '<=', now()->addDays(15))
                  ->orWhere('fecha_vencimiento_carne_salud', '<=', now()->addDays(15))
                  ->orWhere('fecha_vencimiento_carne_habilitante', '<=', now()->addDays(15));
            })->take(4)->get();

        // 4. Actividad Reciente para las Tablas del Dashboard
        $this->ultimosVuelos = Vuelo::withCount('palomas')
            ->latest('fecha')
            ->take(4)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin-dashboard');
    }
}