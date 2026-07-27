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
    public ?int $filtroUbicacionId = null;
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

    public function updatingFiltroUbicacionId(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $lotes = LoteStock::query()
            ->with(['item', 'ubicacion', 'proveedor'])
            ->when($this->busqueda, fn ($q) => $q->whereHas('item', fn ($q) => $q
                ->where('nombre', 'like', "%{$this->busqueda}%")
                ->orWhere('codigo', 'like', "%{$this->busqueda}%")))
            ->when($this->filtroItemId, fn ($q) => $q->where('item_id', $this->filtroItemId))
            ->when($this->filtroUbicacionId, fn ($q) => $q->where('ubicacion_id', $this->filtroUbicacionId))
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
            'ubicaciones' => Ubicacion::orderBy('nombre')->get(),
        ]);
    }
}