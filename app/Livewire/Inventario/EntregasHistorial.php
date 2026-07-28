<?php

namespace App\Livewire\Inventario;

use App\Models\Entrega;
use App\Models\Ubicacion;
use Livewire\Component;
use Livewire\WithPagination;

class EntregasHistorial extends Component
{
    use WithPagination;

    public string $filtroTipo = '';
    public ?int $filtroUbicacionId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Entrega::class);
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroUbicacionId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $entregas = Entrega::query()
            ->with(['ubicacionOrigen', 'ubicacionDestino', 'usuario'])
            ->withCount('movimientos')
            ->when($this->filtroTipo, fn ($q) => $q->where('tipo', $this->filtroTipo))
            ->when($this->filtroUbicacionId, fn ($q) => $q->where(fn ($q) => $q
                ->where('ubicacion_origen_id', $this->filtroUbicacionId)
                ->orWhere('ubicacion_destino_id', $this->filtroUbicacionId)))
            ->latest()
            ->paginate(15);

        return view('livewire.inventario.entregas-historial', [
            'entregas' => $entregas,
            'ubicaciones' => Ubicacion::orderBy('nombre')->get(),
        ]);
    }
}