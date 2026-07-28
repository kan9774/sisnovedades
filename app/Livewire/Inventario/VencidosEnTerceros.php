<?php

namespace App\Livewire\Inventario;

use App\Models\ItemUnidad;
use App\Models\LoteStock;
use App\Models\Ubicacion;
use Livewire\Component;

class VencidosEnTerceros extends Component
{
    public string $busqueda = '';
    public ?int $filtroUbicacionId = null;

    public function mount(): void
    {
        $this->authorize('viewAny-lote');
    }

    public function render()
    {
        $depositos = Ubicacion::where('tipo', 'deposito')->pluck('id');

        $lotesVencidos = LoteStock::query()
            ->whereNotIn('ubicacion_id', $depositos)
            ->where('cantidad_actual', '>', 0)
            ->with(['item', 'ubicacion'])
            ->when($this->filtroUbicacionId, fn ($q) => $q->where('ubicacion_id', $this->filtroUbicacionId))
            ->when($this->busqueda, fn ($q) => $q->whereHas('item', fn ($q) => $q
                ->where('nombre', 'like', "%{$this->busqueda}%")
                ->orWhere('codigo', 'like', "%{$this->busqueda}%")))
            ->get()
            ->filter(fn (LoteStock $lote) => $lote->vencido)
            ->map(fn (LoteStock $lote) => [
                'tipo' => 'cantidad',
                'item' => $lote->item,
                'ubicacion' => $lote->ubicacion,
                'cantidad' => $lote->cantidad_actual,
                'numeroSerie' => null,
                'vencimiento' => $lote->vencimiento,
            ]);

        $unidadesVencidas = ItemUnidad::query()
            ->whereNotIn('ubicacion_actual_id', $depositos)
            ->whereIn('estado', ['disponible', 'asignado'])
            ->with(['item', 'ubicacionActual'])
            ->when($this->filtroUbicacionId, fn ($q) => $q->where('ubicacion_actual_id', $this->filtroUbicacionId))
            ->when($this->busqueda, fn ($q) => $q->whereHas('item', fn ($q) => $q
                ->where('nombre', 'like', "%{$this->busqueda}%")
                ->orWhere('codigo', 'like', "%{$this->busqueda}%")))
            ->get()
            ->filter(fn (ItemUnidad $u) => $u->estaVencida())
            ->map(fn (ItemUnidad $u) => [
                'tipo' => 'individual',
                'item' => $u->item,
                'ubicacion' => $u->ubicacionActual,
                'cantidad' => 1,
                'numeroSerie' => $u->numero_serie,
                'vencimiento' => $u->vencimiento,
            ]);

        $registros = $lotesVencidos->concat($unidadesVencidas)->sortBy('vencimiento')->values();

        return view('livewire.inventario.vencidos-en-terceros', [
            'registros' => $registros,
            'ubicaciones' => Ubicacion::whereIn('tipo', ['persona', 'oficina', 'vehiculo'])->orderBy('nombre')->get(),
        ]);
    }
}