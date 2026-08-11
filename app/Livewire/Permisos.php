<?php

namespace App\Livewire;

use App\Models\Permission;
use App\Traits\UsesBootstrapPagination;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class Permisos extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    public $search = '';
    public $showForm = false;
    public $formTipo = 'create';
    public $formNombre = '';
    public $formDescripcion = '';
    public $formModel = '';
    public $formPermisoId = null;
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;
    public $justSaved = false;
    public $confirmDeleteId = null;

    #[Computed]
    public function permisos()
    {
        $query = Permission::withCount('rols')
            ->orderBy('name');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function modulos()
    {
        return Permission::whereNotNull('model')
            ->distinct()
            ->orderBy('model')
            ->pluck('model');
    }

    public function mount()
    {
        $this->authorize('viewAny', Permission::class);
    }

    public function crear()
    {
        try {
            $this->authorize('create', Permission::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
        $this->dispatch('abrir-modal-permisos');
    }

    public function abrirEditar(int $permisoId)
    {
        $permiso = Permission::findOrFail($permisoId);

        try {
            $this->authorize('update', $permiso);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formPermisoId = $permiso->id;
        $this->formNombre = $permiso->name;
        $this->formDescripcion = $permiso->description;
        $this->formModel = $permiso->model;
        $this->showForm = true;
        $this->dispatch('abrir-modal-permisos');
    }

    public function cerrarForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
        $this->dispatch('cerrar-modal-permisos');
    }

    public function submitForm()
    {
        try {
            if ($this->formTipo === 'create') {
                $this->authorize('create', Permission::class);
            } else {
                $this->authorize('update', Permission::findOrFail($this->formPermisoId));
            }
        } catch (AuthorizationException $e) {
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
                Permission::create([
                    'name' => $this->formNombre,
                    'description' => $this->formDescripcion ?: null,
                    'model' => $this->formModel ?: null,
                ]);
                $this->successMsg = 'Permiso creado correctamente.';
            } else {
                $permiso = Permission::findOrFail($this->formPermisoId);
                $permiso->update([
                    'name' => $this->formNombre,
                    'description' => $this->formDescripcion ?: null,
                    'model' => $this->formModel ?: null,
                ]);
                $this->successMsg = 'Permiso actualizado correctamente.';
            }

            $this->justSaved = true;
            $this->page = 1;
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function confirmDelete(int $permisoId)
    {
        $this->confirmDeleteId = $permisoId;
    }

    public function executeDelete()
    {
        $this->loading = true;
        try {
            $permiso = Permission::findOrFail($this->confirmDeleteId);

            try {
                $this->authorize('delete', $permiso);
            } catch (AuthorizationException $e) {
                $this->errorMsg = $e->getMessage();
                return;
            }

            if ($permiso->rols()->count() > 0) {
                $this->errorMsg = 'No se puede eliminar un permiso asignado a roles.';
                return;
            }

            $permiso->delete();
            $this->successMsg = 'Permiso eliminado correctamente.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->confirmDeleteId = null;
        }
    }

    public function resetForm(): void
    {
        $this->formTipo = 'create';
        $this->formNombre = '';
        $this->formDescripcion = '';
        $this->formModel = '';
        $this->formPermisoId = null;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.permisos.index', [
            'permisos' => $this->permisos(),
            'modulos' => $this->modulos(),
        ]);
    }

    protected function reglasValidacion(): array
    {
        $unique = $this->formTipo === 'create'
            ? 'unique:permissions,name'
            : 'unique:permissions,name,' . $this->formPermisoId;

        return [
            'formNombre' => 'required|string|max:255|' . $unique,
            'formDescripcion' => 'nullable|string|max:255',
            'formModel' => 'nullable|string|max:255',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formNombre.required' => 'El nombre es obligatorio.',
            'formNombre.unique' => 'Ya existe un permiso con ese nombre.',
        ];
    }
}
