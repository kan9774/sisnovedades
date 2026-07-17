<?php declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use App\Models\SalidaVehiculo;
use App\Models\Vuelo;
use App\Models\News;
use App\Models\Conductor;
use Carbon\Carbon;

class DashboardCharts extends Component
{
    public $chartData;

    public function mount()
    {
        $this->chartData = $this->obtenerDatosGraficos();
    }

    public function actualizar()
    {
        $this->chartData = $this->obtenerDatosGraficos();
    }

    protected function obtenerDatosGraficos(): array
    {
        $hoy = Carbon::now();
        $ultimos7dias = collect(range(6, 0))->map(fn($i) => Carbon::now()->subDays($i));

        // 1. Salidas por día (últimos 7 días) - usa guardia_id para relacionar con fecha
        $salidasPorDia = $ultimos7dias->map(function ($dia) {
            return SalidaVehiculo::whereHas('guardia', function($q) use ($dia) {
                $q->whereDate('date', $dia->format('Y-m-d'));
            })->count();
        })->toArray();

        // 2. Vuelos por día (últimos 7 días)
        $vuelosPorDia = $ultimos7dias->map(function ($dia) {
            return Vuelo::whereDate('fecha', $dia->format('Y-m-d'))->count();
        })->toArray();

        // 3. Novedades por tipo (este mes)
        $novedadesPorTipo = News::whereMonth('created_at', $hoy->month)
            ->whereYear('created_at', $hoy->year)
            ->select('type', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type')
            ->toArray();

        // 4. Conductores por estado
        $conductoresPorEstado = [
            'activos' => Conductor::where('activo', true)->count(),
            'inactivos' => Conductor::where('activo', false)->count(),
        ];

        // 5. Vehículos en ruta vs finalizados (hoy)
        $vehiculosHoy = [
            'en_ruta' => SalidaVehiculo::whereHas('guardia', function($q) use ($hoy) {
                $q->whereDate('date', $hoy->format('Y-m-d'));
            })->whereNull('hora_entra')->count(),
            'finalizados' => SalidaVehiculo::whereHas('guardia', function($q) use ($hoy) {
                $q->whereDate('date', $hoy->format('Y-m-d'));
            })->whereNotNull('hora_entra')->count(),
        ];

        return [
            'labels7dias' => $ultimos7dias->map(fn($d) => $d->format('d/m'))->toArray(),
            'salidasPorDia' => $salidasPorDia,
            'vuelosPorDia' => $vuelosPorDia,
            'novedadesPorTipo' => $novedadesPorTipo,
            'conductoresPorEstado' => $conductoresPorEstado,
            'vehiculosHoy' => $vehiculosHoy,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-charts');
    }
}
