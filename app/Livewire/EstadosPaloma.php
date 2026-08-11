<?php

namespace App\Livewire;

use App\Models\EstadoPaloma;
use App\Traits\UsesBootstrapPagination;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class EstadosPaloma extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    // ── Estado de búsqueda ──
    public $search = '';

    // ── Estado del formulario ──
    public $showForm = false;
    public $formTipo = 'create';
    public $formNombre = '';
    public $formColor = '';
    public $formActivo = false;
    public $formEstadoId = null;

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    // ── mount: autorización de acceso ──
    public function mount()
    {
        $this->authorize('viewAny', EstadoPaloma::class);
    }

    // ── Consulta con caché ──
    #[Computed]
    public function estadosPaloma()
    {
        $query = EstadoPaloma::withCount('palomas')
            ->orderBy('nombre');

        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        return $query->paginate(15);
    }

    // ── ABRIR FORMULARIO DE ALTA ──
    public function crear()
    {
        $this->authorize('create', EstadoPaloma::class);

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
    }

    // ── ABRIR FORMULARIO DE EDICIÓN ──
    public function abrirEditar(int $estadoId)
    {
        $estado = EstadoPaloma::findOrFail($estadoId);
        $this->authorize('update', $estado);

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formEstadoId = $estado->id;
        $this->formNombre = $estado->nombre;
        $this->formColor = $estado->color ?? '';
        $this->formActivo = (bool) $estado->activo;
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
                $this->authorize('create', EstadoPaloma::class);
            } else {
                $estado = EstadoPaloma::findOrFail($this->formEstadoId);
                $this->authorize('update', $estado);
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
                EstadoPaloma::create([
                    'nombre' => $this->formNombre,
                    'color' => $this->formColor ?: null,
                    'activo' => $this->formActivo,
                ]);
                $this->successMsg = 'Estado creado correctamente.';
            } else {
                $estado = EstadoPaloma::findOrFail($this->formEstadoId);
                $estado->update([
                    'nombre' => $this->formNombre,
                    'color' => $this->formColor ?: null,
                    'activo' => $this->formActivo,
                ]);
                $this->successMsg = 'Estado actualizado correctamente.';
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
    public function eliminar(int $estadoId)
    {
        $estado = EstadoPaloma::withCount('palomas')->findOrFail($estadoId);
        $this->authorize('delete', $estado);

        if ($estado->palomas_count > 0) {
            $this->errorMsg = 'No se puede eliminar un estado que tiene palomas asociadas.';
            return;
        }

        $estado->delete();
        $this->successMsg = 'Estado eliminado correctamente.';
    }

    // ── RESET DE CAMPOS ──
    protected function resetForm(): void
    {
        $this->formTipo = 'create';
        $this->formNombre = '';
        $this->formColor = '';
        $this->formActivo = false;
        $this->formEstadoId = null;
    }

    // ── REGLAS DE VALIDACIÓN ──
    protected function reglasValidacion(): array
    {
        $unique = $this->formTipo === 'create'
            ? 'unique:estados_paloma,nombre'
            : 'unique:estados_paloma,nombre,' . $this->formEstadoId;

        return [
            'formNombre' => 'required|string|max:100|' . $unique,
            'formColor' => 'nullable|string|max:50',
            'formActivo' => 'boolean',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formNombre.required' => 'El nombre es obligatorio.',
            'formNombre.unique'   => 'Ya existe un estado con ese nombre.',
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
        return view('livewire.palomar.estados.index', [
            'estadosPaloma' => $this->estadosPaloma(),
        ]);
    }
}
