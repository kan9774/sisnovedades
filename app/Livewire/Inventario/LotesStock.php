<?php

namespace App\Livewire\Inventario;

use App\Models\Item;
use App\Models\LoteStock;
use App\Models\Ubicacion;
use Livewire\Component;
use Livewire\WithPagination;

class LotesStock extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public ?int $filtroItemId = null;
    public string $filtroEstado = ''; // '', 'vencidos', 'con_stock'

    public function mount(): void
    {
        $this->authorize('viewAny-lote');
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroItemId(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $depositos = Ubicacion::where('tipo', 'deposito')->pluck('id');

        $lotes = LoteStock::query()
            ->whereIn('ubicacion_id', $depositos)
            ->with(['item', 'ubicacion', 'proveedor'])
            ->when($this->busqueda, fn ($q) => $q->whereHas('item', fn ($q) => $q
                ->where('nombre', 'like', "%{$this->busqueda}%")
                ->orWhere('codigo', 'like', "%{$this->busqueda}%")))
            ->when($this->filtroItemId, fn ($q) => $q->where('item_id', $this->filtroItemId))
            ->when($this->filtroEstado === 'con_stock', fn ($q) => $q->where('cantidad_actual', '>', 0))
            ->orderBy('fecha_recibido')
            ->orderBy('id')
            ->paginate(15);

        // El filtro "vencidos" depende del accessor calculado (fecha_recibido +
        // vida_util_meses del item), así que se filtra en PHP tras paginar
        // la colección de la página actual, no vía query SQL.
        if ($this->filtroEstado === 'vencidos') {
            $lotes->setCollection(
                $lotes->getCollection()->filter(fn (LoteStock $lote) => $lote->vencido)
            );
        }

        return view('livewire.inventario.lotes-stock', [
            'lotes' => $lotes,
            'items' => Item::where('tipo_seguimiento', 'cantidad')->orderBy('nombre')->get(),
            'resumenReposicion' => $this->calcularResumenReposicion($depositos),
        ]);
    }

    /**
     * Por cada item con lotes en depósito, suma cuánto queda vigente vs.
     * vencido, y marca si el stock vigente ya cayó al/bajo el mínimo
     * configurado. Solo se listan los items que necesitan atención (algo
     * vencido, o por debajo del mínimo) — no todo el catálogo.
     */
    private function calcularResumenReposicion($depositos)
    {
        return LoteStock::whereIn('ubicacion_id', $depositos)
            ->where('cantidad_actual', '>', 0)
            ->with('item')
            ->get()
            ->groupBy('item_id')
            ->map(function ($lotesDelItem) {
                $item = $lotesDelItem->first()->item;
                $vigente = $lotesDelItem->filter(fn (LoteStock $l) => ! $l->vencido)->sum('cantidad_actual');
                $vencido = $lotesDelItem->filter(fn (LoteStock $l) => $l->vencido)->sum('cantidad_actual');

                return [
                    'item' => $item,
                    'vigente' => $vigente,
                    'vencido' => $vencido,
                    'bajoMinimo' => $item->stock_minimo !== null && $vigente <= $item->stock_minimo,
                ];
            })
            ->filter(fn ($r) => $r['vencido'] > 0 || $r['bajoMinimo'])
            ->sortByDesc(fn ($r) => $r['vencido'])
            ->values();
    }
}