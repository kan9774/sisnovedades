<?php

namespace App\Livewire\Inventario;

use App\Exceptions\StockInsuficienteException;
use App\Models\Item;
use App\Models\ItemUnidad;
use App\Models\Ubicacion;
use App\Models\User;
use App\Services\InventarioService;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithPagination;

class UnidadesIndividuales extends Component
{
    use WithPagination;

    // Filtros del listado
    public string $busqueda = '';
    public ?int $filtroItemId = null;
    public string $filtroEstado = '';

    // Modal: alta de unidad nueva
    public bool $mostrarModalAlta = false;
    public ?int $altaItemId = null;
    public ?string $altaNumeroSerie = null;
    public ?int $altaUbicacionId = null;
    public ?string $altaMotivo = null;

    // Modal: asignar / transferir
    public bool $mostrarModalAsignar = false;
    public ?int $unidadId = null;
    public ?int $asignarUbicacionId = null;
    public ?int $asignarResponsableId = null;
    public ?string $asignarMotivo = null;

    // Modal: dar de baja
    public bool $mostrarModalBaja = false;
    public ?string $bajaMotivo = null;

    public function mount(): void
    {
        $this->authorize('viewAny', ItemUnidad::class);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------
    | Alta de unidad
    |--------------------------------------------------------------------
    */

    public function abrirModalAlta(): void
    {
        $this->authorize('create', ItemUnidad::class);
        $this->reset(['altaItemId', 'altaNumeroSerie', 'altaUbicacionId', 'altaMotivo']);
        $this->resetErrorBag();
        $this->mostrarModalAlta = true;
        $this->dispatch('abrir-modal-unidad-alta');
    }

    public function darDeAlta(): void
    {
        $this->authorize('create', ItemUnidad::class);

        $datos = $this->validate([
            'altaItemId' => 'required|exists:items,id',
            'altaNumeroSerie' => 'nullable|string|max:100',
            'altaUbicacionId' => 'required|exists:ubicaciones,id',
            'altaMotivo' => 'nullable|string|max:255',
        ], [
            'altaItemId.required' => 'Seleccioná el ítem al que pertenece la unidad.',
            'altaUbicacionId.required' => 'Seleccioná la ubicación inicial.',
        ]);

        $item = Item::findOrFail($datos['altaItemId']);

        if (! $item->esIndividual()) {
            $this->addError('altaItemId', 'Este ítem no es de seguimiento individual.');
            return;
        }

        app(InventarioService::class)->darDeAltaUnidad(
            $item,
            Ubicacion::findOrFail($datos['altaUbicacionId']),
            Auth::user(),
            $datos['altaNumeroSerie'],
            $datos['altaMotivo'],
        );

        session()->flash('success', 'Unidad dada de alta correctamente.');
        $this->mostrarModalAlta = false;
        $this->dispatch('cerrar-modal-unidad');
    }

    /*
    |--------------------------------------------------------------------
    | Asignar / transferir
    |--------------------------------------------------------------------
    */

    public function abrirModalAsignar(int $unidadId): void
    {
        $unidad = ItemUnidad::findOrFail($unidadId);
        $this->authorize('asignar', $unidad);

        $this->unidadId = $unidad->id;
        $this->reset(['asignarUbicacionId', 'asignarResponsableId', 'asignarMotivo']);
        $this->resetErrorBag();
        $this->mostrarModalAsignar = true;
        $this->dispatch('abrir-modal-unidad-asignar');
    }

    public function asignar(): void
    {
        $unidad = ItemUnidad::findOrFail($this->unidadId);
        $this->authorize('asignar', $unidad);

        $datos = $this->validate([
            'asignarUbicacionId' => 'required|exists:ubicaciones,id',
            'asignarResponsableId' => 'nullable|exists:users,id',
            'asignarMotivo' => 'nullable|string|max:255',
        ], [
            'asignarUbicacionId.required' => 'Seleccioná la ubicación de destino.',
        ]);

        try {
            app(InventarioService::class)->asignarUnidad(
                $unidad,
                Ubicacion::findOrFail($datos['asignarUbicacionId']),
                Auth::user(),
                $datos['asignarMotivo'],
            );
        } catch (InvalidArgumentException $e) {
            $this->addError('asignarUbicacionId', $e->getMessage());
            return;
        }

        // El responsable directo es un dato propio de la unidad, no del
        // movimiento — se guarda aparte si se indicó uno.
        if ($datos['asignarResponsableId']) {
            $unidad->update(['responsable_id' => $datos['asignarResponsableId']]);
        }

        session()->flash('success', 'Unidad asignada correctamente.');
        $this->mostrarModalAsignar = false;
        $this->dispatch('cerrar-modal-unidad');
    }

    /*
    |--------------------------------------------------------------------
    | Reparación
    |--------------------------------------------------------------------
    */

    public function marcarEnReparacion(int $unidadId): void
    {
        $unidad = ItemUnidad::findOrFail($unidadId);
        $this->authorize('marcarEnReparacion', $unidad);

        app(InventarioService::class)->marcarEnReparacion($unidad, Auth::user());

        session()->flash('success', 'Unidad marcada como en reparación.');
    }

    /*
    |--------------------------------------------------------------------
    | Baja
    |--------------------------------------------------------------------
    */

    public function abrirModalBaja(int $unidadId): void
    {
        $unidad = ItemUnidad::findOrFail($unidadId);
        $this->authorize('darDeBaja', $unidad);

        $this->unidadId = $unidad->id;
        $this->bajaMotivo = null;
        $this->resetErrorBag();
        $this->mostrarModalBaja = true;
        $this->dispatch('abrir-modal-unidad-baja');
    }

    public function confirmarBaja(): void
    {
        $unidad = ItemUnidad::findOrFail($this->unidadId);
        $this->authorize('darDeBaja', $unidad);

        $datos = $this->validate([
            'bajaMotivo' => 'required|string|max:255',
        ], [
            'bajaMotivo.required' => 'Indicá el motivo de la baja (rotura, pérdida, etc).',
        ]);

        try {
            app(InventarioService::class)->darDeBajaUnidad($unidad, Auth::user(), $datos['bajaMotivo']);
        } catch (InvalidArgumentException $e) {
            $this->addError('bajaMotivo', $e->getMessage());
            return;
        }

        session()->flash('success', 'Unidad dada de baja.');
        $this->mostrarModalBaja = false;
        $this->dispatch('cerrar-modal-unidad');
    }

    public function cerrarModales(): void
    {
        $this->mostrarModalAlta = false;
        $this->mostrarModalAsignar = false;
        $this->mostrarModalBaja = false;
        $this->dispatch('cerrar-modal-unidad');
    }

    public function render()
    {
        $unidades = ItemUnidad::query()
            ->with(['item', 'ubicacionActual', 'responsable'])
            ->when($this->busqueda, fn ($q) => $q->where('numero_serie', 'like', "%{$this->busqueda}%"))
            ->when($this->filtroItemId, fn ($q) => $q->where('item_id', $this->filtroItemId))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->latest()
            ->paginate(15);

        return view('livewire.inventario.unidades-individuales', [
            'unidades' => $unidades,
            'items' => Item::where('tipo_seguimiento', 'individual')->orderBy('nombre')->get(),
            'ubicaciones' => Ubicacion::orderBy('nombre')->get(),
            'usuarios' => User::orderBy('name')->get(),
        ]);
    }
}