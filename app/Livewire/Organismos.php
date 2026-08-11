<?php

namespace App\Livewire;

use App\Models\Organismo;
use App\Traits\UsesBootstrapPagination;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class Organismos extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    // ── Estado de búsqueda ──
    public $search = '';

    // ── Estado del formulario ──
    public $showForm = false;
    public $formTipo = 'create';
    public $formNombre = '';
    public $formOrganismoId = null;

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    // ── mount: autorización de acceso ──
    public function mount()
    {
        $this->authorize('viewAny', Organismo::class);
    }

    // ── Consulta con caché ──
    #[Computed]
    public function organismos()
    {
        $query = Organismo::withCount('novedades')
            ->orderBy('name');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->paginate(15);
    }

    // ── ABRIR FORMULARIO DE ALTA ──
    public function crear()
    {
        $this->authorize('create', Organismo::class);

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
    }

    // ── ABRIR FORMULARIO DE EDICIÓN ──
    public function abrirEditar(int $organismoId)
    {
        $organismo = Organismo::findOrFail($organismoId);
        $this->authorize('update', $organismo);

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formOrganismoId = $organismo->id;
        $this->formNombre = $organismo->name;
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
                $this->authorize('create', Organismo::class);
            } else {
                $organismo = Organismo::findOrFail($this->formOrganismoId);
                $this->authorize('update', $organismo);
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
                Organismo::create([
                    'name' => $this->formNombre,
                ]);
                $this->successMsg = 'Organismo creado correctamente.';
            } else {
                $organismo = Organismo::findOrFail($this->formOrganismoId);
                $organismo->update([
                    'name' => $this->formNombre,
                ]);
                $this->successMsg = 'Organismo actualizado correctamente.';
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
    public function eliminar(int $organismoId)
    {
        $organismo = Organismo::withCount('novedades')->findOrFail($organismoId);
        $this->authorize('delete', $organismo);

        if ($organismo->novedades_count > 0) {
            $this->errorMsg = 'No se puede eliminar un organismo con novedades asociadas.';
            return;
        }

        $organismo->delete();
        $this->successMsg = 'Organismo eliminado correctamente.';
    }

    // ── RESET DE CAMPOS ──
    protected function resetForm(): void
    {
        $this->formTipo = 'create';
        $this->formNombre = '';
        $this->formOrganismoId = null;
    }

    // ── REGLAS DE VALIDACIÓN ──
    protected function reglasValidacion(): array
    {
        $unique = $this->formTipo === 'create'
            ? 'unique:organismos,name'
            : 'unique:organismos,name,' . $this->formOrganismoId;

        return [
            'formNombre' => 'required|string|max:255|' . $unique,
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formNombre.required' => 'El nombre es obligatorio.',
            'formNombre.unique'   => 'Ya existe un organismo con ese nombre.',
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
        return view('livewire.organismos.index', [
            'organismos' => $this->organismos(),
        ]);
    }
}
