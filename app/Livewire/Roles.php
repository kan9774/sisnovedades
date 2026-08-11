<?php

namespace App\Livewire;

use App\Models\Permission;
use App\Models\Rol;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;

class Roles extends Component
{
    public $search = '';
    public $showForm = false;
    public $formTipo = 'create';
    public $formNombre = '';
    public $formDescripcion = '';
    public $formRolId = null;
    public $formPermisosSeleccionados = [];
    public $permisosPorModulo = [];
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;
    public $justSaved = false;
    public $confirmDeleteId = null;

    public function mount()
    {
        $this->authorize('viewAny', Rol::class);
    }

    public function crear()
    {
        try {
            $this->authorize('create', Rol::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetForm();
        $this->formTipo = 'create';
        $this->permisosPorModulo = $this->agruparPermisosPorModulo(Permission::all());
        $this->showForm = true;
        $this->resetErrorBag();
        $this->dispatch('abrir-modal-roles');
    }

    public function abrirEditar(int $rolId)
    {
        $rol = Rol::findOrFail($rolId);

        try {
            $this->authorize('update', $rol);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formRolId = $rol->id;
        $this->formNombre = $rol->name;
        $this->formDescripcion = $rol->description;
        $this->formPermisosSeleccionados = $rol->permisos->pluck('id')->toArray();
        $this->permisosPorModulo = $this->agruparPermisosPorModulo(Permission::all());
        $this->showForm = true;
        $this->dispatch('abrir-modal-roles');
    }

    public function cerrarForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
        $this->dispatch('cerrar-modal-roles');
    }

    public function submitForm()
    {
        try {
            if ($this->formTipo === 'create') {
                $this->authorize('create', Rol::class);
            } else {
                $this->authorize('update', Rol::findOrFail($this->formRolId));
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
                $rol = Rol::create([
                    'name' => $this->formNombre,
                    'description' => $this->formDescripcion ?: null,
                ]);

                if (!empty($this->formPermisosSeleccionados)) {
                    $rol->permisos()->sync($this->formPermisosSeleccionados);
                }

                $this->successMsg = 'Rol creado correctamente.';
            } else {
                $rol = Rol::findOrFail($this->formRolId);
                $rol->update([
                    'name' => $this->formNombre,
                    'description' => $this->formDescripcion ?: null,
                ]);

                $rol->permisos()->sync($this->formPermisosSeleccionados);

                $this->successMsg = 'Rol actualizado correctamente.';
            }

            $this->justSaved = true;
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function confirmDelete(int $rolId)
    {
        $this->confirmDeleteId = $rolId;
    }

    public function executeDelete()
    {
        $this->loading = true;
        try {
            $rol = Rol::findOrFail($this->confirmDeleteId);

            try {
                $this->authorize('delete', $rol);
            } catch (AuthorizationException $e) {
                $this->errorMsg = $e->getMessage();
                return;
            }

            if ($rol->name === 'admin') {
                $this->errorMsg = 'No se puede eliminar el rol admin.';
                return;
            }

            if ($rol->users()->count() > 0) {
                $this->errorMsg = 'No se puede eliminar un rol con usuarios asignados.';
                return;
            }

            $rol->permisos()->detach();
            $rol->delete();
            $this->successMsg = 'Rol eliminado correctamente.';
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
        $this->formRolId = null;
        $this->formPermisosSeleccionados = [];
    }

    public function clearFilters()
    {
        $this->search = '';
    }

    public function render()
    {
        $query = Rol::with('permisos')
            ->withCount('users')
            ->where('name', '!=', 'admin');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }

        return view('livewire.roles.index', [
            'roles' => $query->get(),
        ]);
    }

    protected function reglasValidacion(): array
    {
        $unique = $this->formTipo === 'create'
            ? 'unique:rols,name'
            : 'unique:rols,name,' . $this->formRolId;

        return [
            'formNombre' => 'required|string|max:255|' . $unique,
            'formDescripcion' => 'nullable|string|max:255',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formNombre.required' => 'El nombre es obligatorio.',
            'formNombre.unique' => 'Ya existe un rol con ese nombre.',
        ];
    }

    private function agruparPermisosPorModulo($permisos)
    {
        return $permisos
            ->groupBy(fn ($permiso) => $permiso->model ?: 'General')
            ->sortKeys()
            ->mapWithKeys(function ($grupoDePermisos, $modelo) {
                return [$this->formatearNombreModulo($modelo) => $grupoDePermisos];
            });
    }

    private function formatearNombreModulo(string $modelo): string
    {
        if ($modelo === 'General') {
            return 'General';
        }

        return trim(preg_replace('/(?<!^)(?=[A-Z])/', ' ', $modelo));
    }
}
