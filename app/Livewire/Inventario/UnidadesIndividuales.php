<?php

namespace App\Livewire\Inventario;

use App\Exceptions\StockInsuficienteException;
use App\Models\Item;
use App\Models\ItemUnidad;
use App\Models\Proveedor;
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
    public ?int $altaProveedorId = null;
    public ?string $altaFechaRecibido = null;
    public ?string $altaMotivo = null;

    // Modal: asignar / transferir
    public bool $mostrarModalAsignar = false;
    public ?int $unidadId = null;
    public ?int $asignarUbicacionId = null;
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
        $this->reset(['altaItemId', 'altaNumeroSerie', 'altaUbicacionId', 'altaProveedorId', 'altaFechaRecibido', 'altaMotivo']);
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
            'altaProveedorId' => 'nullable|exists:proveedores,id',
            'altaFechaRecibido' => 'nullable|date|before_or_equal:today',
            'altaMotivo' => 'nullable|string|max:255',
        ], [
            'altaItemId.required' => 'Seleccioná el ítem al que pertenece la unidad.',
            'altaUbicacionId.required' => 'Seleccioná la ubicación inicial.',
            'altaFechaRecibido.before_or_equal' => 'La fecha de recibido no puede ser futura.',
        ]);

        $item = Item::findOrFail($datos['altaItemId']);

        if (! $item->esIndividual()) {
            $this->addError('altaItemId', 'Este ítem no es de seguimiento individual.');
            return;
        }

        try {
            app(InventarioService::class)->darDeAltaUnidad(
                $item,
                Ubicacion::findOrFail($datos['altaUbicacionId']),
                Auth::user(),
                $datos['altaNumeroSerie'],
                $datos['altaMotivo'],
                $datos['altaProveedorId'] ? Proveedor::findOrFail($datos['altaProveedorId']) : null,
                $datos['altaFechaRecibido'],
            );
        } catch (InvalidArgumentException $e) {
            $this->addError('altaUbicacionId', $e->getMessage());
            return;
        }

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
        $this->reset(['asignarUbicacionId', 'asignarMotivo']);
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

        // El responsable directo siempre es quien realiza la asignación,
        // nunca un valor elegido por el usuario.
        $unidad->update(['responsable_id' => Auth::id()]);

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

        try {
            app(InventarioService::class)->marcarEnReparacion($unidad, Auth::user());
        } catch (InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

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

    public function itemSeleccionadoTieneVidaUtil(): bool
    {
        if (! $this->altaItemId) {
            return false;
        }

        return (bool) Item::find($this->altaItemId)?->vida_util_meses;
    }

    public function vidaUtilDelItemSeleccionado(): ?int
    {
        if (! $this->altaItemId) {
            return null;
        }

        return Item::find($this->altaItemId)?->vida_util_meses;
    }

    public function render()
    {
        $unidades = ItemUnidad::query()
            ->with(['item', 'ubicacionActual', 'responsable', 'proveedor'])
            ->when($this->busqueda, fn($q) => $q->where('numero_serie', 'like', "%{$this->busqueda}%"))
            ->when($this->filtroItemId, fn($q) => $q->where('item_id', $this->filtroItemId))
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->latest()
            ->paginate(15);

        return view('livewire.inventario.unidades-individuales', [
            'unidades' => $unidades,
            'items' => Item::where('tipo_seguimiento', 'individual')->orderBy('nombre')->get(),
            'ubicaciones' => Ubicacion::orderBy('nombre')->get(),
            'usuarios' => User::orderBy('name')->get(),
            'proveedores' => Proveedor::orderBy('nombre')->get(),
        ]);
    }
    public function volverDeReparacion(int $unidadId): void
    {
        $unidad = ItemUnidad::findOrFail($unidadId);
        $this->authorize('volverDeReparacion', $unidad); // ⚠️ falta en la Policy, ver abajo

        try {
            app(InventarioService::class)->volverDeReparacion($unidad, Auth::user());
        } catch (InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        session()->flash('success', 'Unidad disponible nuevamente.');
    }
}
