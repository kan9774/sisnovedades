<?php

namespace App\Livewire;

use App\Models\TipoApoyo;
use App\Traits\UsesBootstrapPagination;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class TiposApoyo extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    public $search = '';
    public $showForm = false;
    public $formTipo = 'create';
    public $formNombre = '';
    public $formColor = '#28a745';
    public $formTipoApoyoId = null;
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;
    public $confirmDeleteId = null;

    #[Computed]
    public function tiposApoyo()
    {
        $query = TipoApoyo::withCount('apoyos')
            ->orderBy('nombre');

        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        return $query->paginate(15);
    }

    public function mount()
    {
        $this->authorize('viewAny', TipoApoyo::class);
    }

    public function crear()
    {
        try {
            $this->authorize('create', TipoApoyo::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        // Toggle: si el form inline ya está abierto en modo create, cerrarlo.
        if ($this->showForm && $this->formTipo === 'create') {
            $this->cerrarForm();
            return;
        }

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
        $this->errorMsg = '';
    }

    public function abrirEditar(int $tipoApoyoId)
    {
        $tipoApoyo = TipoApoyo::findOrFail($tipoApoyoId);

        try {
            $this->authorize('update', $tipoApoyo);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formTipoApoyoId = $tipoApoyo->id;
        $this->formNombre = $tipoApoyo->nombre;
        $this->formColor = $tipoApoyo->color;
        $this->showForm = true;
        $this->errorMsg = '';
    }

    public function cerrarForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
    }

    public function guardar()
    {
        try {
            if ($this->formTipo === 'create') {
                $this->authorize('create', TipoApoyo::class);
            } else {
                $this->authorize('update', TipoApoyo::findOrFail($this->formTipoApoyoId));
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
                TipoApoyo::create([
                    'nombre' => $this->formNombre,
                    'color' => $this->formColor,
                ]);
                $this->successMsg = 'Tipo de apoyo creado correctamente.';
            } else {
                $tipoApoyo = TipoApoyo::findOrFail($this->formTipoApoyoId);
                $tipoApoyo->update([
                    'nombre' => $this->formNombre,
                    'color' => $this->formColor,
                ]);
                $this->successMsg = 'Tipo de apoyo actualizado correctamente.';
            }

            $this->page = 1;

            // Form inline: al guardar se cierra y vuelve a verse la tabla.
            // successMsg queda seteado para el toast (watcher en la vista).
            $this->showForm = false;
            $this->resetForm();
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function confirmDelete(int $tipoApoyoId)
    {
        $this->confirmDeleteId = $tipoApoyoId;
    }

    public function executeDelete()
    {
        $this->loading = true;
        try {
            $tipoApoyo = TipoApoyo::findOrFail($this->confirmDeleteId);

            try {
                $this->authorize('delete', $tipoApoyo);
            } catch (AuthorizationException $e) {
                $this->errorMsg = $e->getMessage();
                return;
            }

            if ($tipoApoyo->apoyos()->count() > 0) {
                $this->errorMsg = 'No se puede eliminar este tipo porque tiene apoyos asociados.';
                return;
            }

            $tipoApoyo->delete();
            $this->successMsg = 'Tipo de apoyo eliminado correctamente.';
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
        $this->formColor = '#28a745';
        $this->formTipoApoyoId = null;
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
        return view('livewire.apoyos.tipos.index', [
            'tiposApoyo' => $this->tiposApoyo(),
        ]);
    }

    protected function reglasValidacion(): array
    {
        $unique = $this->formTipo === 'create'
            ? 'unique:tipos_apoyo,nombre'
            : 'unique:tipos_apoyo,nombre,' . $this->formTipoApoyoId;

        return [
            'formNombre' => 'required|string|max:255|' . $unique,
            'formColor' => 'required|string|max:7',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formNombre.required' => 'El nombre es obligatorio.',
            'formNombre.unique' => 'Ya existe un tipo de apoyo con ese nombre.',
            'formColor.required' => 'El color es obligatorio.',
        ];
    }
}
