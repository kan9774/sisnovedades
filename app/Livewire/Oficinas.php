<?php

namespace App\Livewire;

use App\Models\Oficina;
use App\Traits\UsesBootstrapPagination;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class Oficinas extends Component
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
    public $formOficinaId = null;

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    // ── mount: autorización de acceso ──
    public function mount()
    {
        $this->authorize('viewAny', Oficina::class);
    }

    // ── Consulta con caché ──
    #[Computed]
    public function oficinas()
    {
        $query = Oficina::withCount('users')
            ->orderBy('nombre');

        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        return $query->paginate(15);
    }

    // ── ABRIR FORMULARIO DE ALTA ──
    public function crear()
    {
        $this->authorize('create', Oficina::class);

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
    }

    // ── ABRIR FORMULARIO DE EDICIÓN ──
    public function abrirEditar(int $oficinaId)
    {
        $oficina = Oficina::findOrFail($oficinaId);
        $this->authorize('update', $oficina);

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formOficinaId = $oficina->id;
        $this->formNombre = $oficina->nombre;
        $this->formActivo = (bool) $oficina->activo;
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
                $this->authorize('create', Oficina::class);
            } else {
                $oficina = Oficina::findOrFail($this->formOficinaId);
                $this->authorize('update', $oficina);
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
                Oficina::create([
                    'nombre' => $this->formNombre,
                    'activo' => $this->formActivo,
                ]);
                $this->successMsg = 'Oficina creada correctamente.';
            } else {
                $oficina = Oficina::findOrFail($this->formOficinaId);
                $oficina->update([
                    'nombre' => $this->formNombre,
                    'activo' => $this->formActivo,
                ]);
                $this->successMsg = 'Oficina actualizada correctamente.';
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
    public function eliminar(int $oficinaId)
    {
        $oficina = Oficina::withCount('users')->findOrFail($oficinaId);
        $this->authorize('delete', $oficina);

        if ($oficina->users_count > 0) {
            $this->errorMsg = 'No se puede eliminar: la oficina tiene usuarios asignados.';
            return;
        }

        $oficina->delete();
        $this->successMsg = 'Oficina eliminada correctamente.';
    }

    // ── RESET DE CAMPOS ──
    protected function resetForm(): void
    {
        $this->formTipo = 'create';
        $this->formNombre = '';
        $this->formActivo = false;
        $this->formOficinaId = null;
    }

    // ── REGLAS DE VALIDACIÓN ──
    protected function reglasValidacion(): array
    {
        $unique = $this->formTipo === 'create'
            ? 'unique:oficinas,nombre'
            : 'unique:oficinas,nombre,' . $this->formOficinaId;

        return [
            'formNombre' => 'required|string|max:150|' . $unique,
            'formActivo' => 'boolean',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formNombre.required' => 'El nombre es obligatorio.',
            'formNombre.unique'   => 'Ya existe una oficina con ese nombre.',
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
        return view('livewire.oficinas.index', [
            'oficinas' => $this->oficinas(),
        ]);
    }
}
