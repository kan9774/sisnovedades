<?php

namespace App\Livewire;

use App\Models\TipoVehiculo;
use App\Traits\UsesBootstrapPagination;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class TiposVehiculo extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    // ── Estado de búsqueda ──
    public $search = '';

    // ── Estado del formulario ──
    public $showForm = false;
    public $formTipo = 'create';
    public $formNombre = '';
    public $formActivo = false;
    public $formTipoVehiculoId = null;

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    // ── mount: autorización de acceso ──
    public function mount()
    {
        $this->authorize('viewAny', TipoVehiculo::class);
    }

    // ── Consulta con caché ──
    #[Computed]
    public function tiposVehiculo()
    {
        $query = TipoVehiculo::withCount('vehiculos')
            ->orderBy('nombre');

        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        return $query->paginate(15);
    }

    // ── ABRIR FORMULARIO DE ALTA ──
    public function crear()
    {
        $this->authorize('create', TipoVehiculo::class);

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
    }

    // ── ABRIR FORMULARIO DE EDICIÓN ──
    public function abrirEditar(int $tipoVehiculoId)
    {
        $tipoVehiculo = TipoVehiculo::findOrFail($tipoVehiculoId);
        $this->authorize('update', $tipoVehiculo);

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formTipoVehiculoId = $tipoVehiculo->id;
        $this->formNombre = $tipoVehiculo->nombre;
        $this->formActivo = (bool) $tipoVehiculo->activo;
        $this->showForm = true;
    }

    // ── CERRAR FORMULARIO ──
    public function cerrarForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
    }

    // ── GUARDAR (create o update) ──
    public function guardar()
    {
        try {
            if ($this->formTipo === 'create') {
                $this->authorize('create', TipoVehiculo::class);
            } else {
                $tipoVehiculo = TipoVehiculo::findOrFail($this->formTipoVehiculoId);
                $this->authorize('update', $tipoVehiculo);
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->validate(
            $this->reglasValidacion(),
            $this->mensajesValidacion()
        );

        $this->loading = true;

        try {
            if ($this->formTipo === 'create') {
                TipoVehiculo::create([
                    'nombre' => $this->formNombre,
                    'activo' => $this->formActivo,
                ]);
                $this->successMsg = 'Tipo de vehículo creado correctamente.';
            } else {
                $tipoVehiculo = TipoVehiculo::findOrFail($this->formTipoVehiculoId);
                $tipoVehiculo->update([
                    'nombre' => $this->formNombre,
                    'activo' => $this->formActivo,
                ]);
                $this->successMsg = 'Tipo de vehículo actualizado correctamente.';
            }

            $this->showForm = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // ── ELIMINAR ──
    public function eliminar(int $tipoVehiculoId)
    {
        $tipoVehiculo = TipoVehiculo::withCount('vehiculos')->findOrFail($tipoVehiculoId);
        $this->authorize('delete', $tipoVehiculo);

        if ($tipoVehiculo->vehiculos_count > 0) {
            $this->errorMsg = 'No se puede eliminar un tipo de vehículo con vehículos asociados.';
            return;
        }

        $tipoVehiculo->delete();
        $this->successMsg = 'Tipo de vehículo eliminado correctamente.';
    }

    // ── RESET DE CAMPOS ──
    protected function resetForm(): void
    {
        $this->formTipo = 'create';
        $this->formNombre = '';
        $this->formActivo = false;
        $this->formTipoVehiculoId = null;
    }

    // ── REGLAS DE VALIDACIÓN ──
    protected function reglasValidacion(): array
    {
        $unique = $this->formTipo === 'create'
            ? 'unique:tipos_vehiculo,nombre'
            : 'unique:tipos_vehiculo,nombre,' . $this->formTipoVehiculoId;

        return [
            'formNombre' => 'required|string|max:50|' . $unique,
            'formActivo' => 'boolean',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formNombre.required' => 'El nombre es obligatorio.',
            'formNombre.unique'   => 'Ya existe un tipo de vehículo con ese nombre.',
        ];
    }

    // ── REACTIVO: al cambiar búsqueda, resetear página ──
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // ── RENDER ──
    public function render()
    {
        return view('livewire.vehiculos.tipos.index', [
            'tiposVehiculo' => $this->tiposVehiculo(),
        ]);
    }
}
