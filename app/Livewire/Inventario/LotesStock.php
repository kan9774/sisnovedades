<?php

namespace App\Livewire\Inventario;

use App\Models\Item;
use App\Models\ItemUnidad;
use App\Models\LoteStock;
use App\Models\Ubicacion;
use Livewire\Component;
use Livewire\WithPagination;

class LotesStock extends Component
{
    use WithPagination;

    // 'lotes' (ítems por cantidad, vía LoteStock) o
    // 'individuales' (ítems individuales, vía ItemUnidad)
    public string $tab = 'lotes';

    public string $busqueda = '';
    public ?int $filtroItemId = null;
    public string $filtroEstado = ''; // '', 'vencidos', 'con_stock'

    public function mount(): void
    {
        $this->authorize('viewAny-lote');
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage('lotesPage');
        $this->resetPage('unidadesPage');
    }

    public function updatingFiltroItemId(): void
    {
        $this->resetPage('lotesPage');
        $this->resetPage('unidadesPage');
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage('lotesPage');
        $this->resetPage('unidadesPage');
    }

    public function updatedTab(): void
    {
        // Cada tab filtra sobre un catálogo de ítems distinto
        // (cantidad vs. individual), así que el filtro por ítem
        // no tiene sentido conservado al cambiar de tab.
        $this->reset(['filtroItemId', 'busqueda', 'filtroEstado']);
        $this->resetPage('lotesPage');
        $this->resetPage('unidadesPage');
    }

    public function render()
    {
        $depositos = Ubicacion::where('tipo', 'deposito')->pluck('id');

        $lotes = null;
        $unidades = null;

        if ($this->tab === 'lotes') {
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
                ->paginate(15, ['*'], 'lotesPage');

            // El filtro "vencidos" depende del accessor calculado, así que
            // se filtra en PHP tras paginar la página actual.
            if ($this->filtroEstado === 'vencidos') {
                $lotes->setCollection(
                    $lotes->getCollection()->filter(fn (LoteStock $lote) => $lote->vencido)
                );
            }
        }

        if ($this->tab === 'individuales') {
            $unidades = ItemUnidad::query()
                ->whereIn('ubicacion_actual_id', $depositos)
                ->whereIn('estado', ['disponible', 'asignado'])
                ->with('item')
                ->when($this->busqueda, fn ($q) => $q->where(function ($q) {
                    $q->whereHas('item', fn ($q) => $q
                        ->where('nombre', 'like', "%{$this->busqueda}%")
                        ->orWhere('codigo', 'like', "%{$this->busqueda}%"))
                        ->orWhere('numero_serie', 'like', "%{$this->busqueda}%");
                }))
                ->when($this->filtroItemId, fn ($q) => $q->where('item_id', $this->filtroItemId))
                ->orderBy('fecha_recibido')
                ->paginate(15, ['*'], 'unidadesPage');

            // Mismo motivo que arriba: vencido es un accessor calculado
            // (fecha_recibido del ítem + vida_util_meses), no una columna.
            if ($this->filtroEstado === 'vencidos') {
                $unidades->setCollection(
                    $unidades->getCollection()->filter(fn (ItemUnidad $u) => $u->estaVencida())
                );
            }
        }

        return view('livewire.inventario.lotes-stock', [
            'lotes' => $lotes,
            'unidades' => $unidades,
            'items' => Item::where('tipo_seguimiento', $this->tab === 'individuales' ? 'individual' : 'cantidad')
                ->orderBy('nombre')
                ->get(),
            'resumenReposicion' => $this->tab === 'lotes'
                ? $this->calcularResumenReposicion($depositos)
                : collect(),
        ]);
    }

    /**
     * Por cada item con lotes en depósito, suma cuánto queda vigente vs.
     * vencido, y marca si el stock vigente ya cayó al/bajo el mínimo
     * configurado. Solo se listan los items que necesitan atención (algo
     * vencido, o por debajo del mínimo) — no todo el catálogo.
     *
     * Nota: esto solo aplica a ítems por cantidad (LoteStock). Los ítems
     * individuales no tienen "stock mínimo" en el mismo sentido.
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