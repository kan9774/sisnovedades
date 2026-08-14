<?php

namespace App\Livewire;

use App\Models\Palomar;
use App\Traits\UsesBootstrapPagination;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Palomares extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    // ── Estado de búsqueda ──
    public $search = '';

    // ── Estado del formulario ──
    public $showForm = false;
    public $formTipo = 'create';
    public $formPalomarId = null;

    // ── Datos del formulario ──
    public $formNombre = '';
    public $formUbicacion = '';
    public $formCapacidadMaxima = null;
    public $formObservaciones = '';
    public $formActivo = false;

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    // ── Confirmación de eliminación ──
    public $confirmDeleteId = null;

    // ── mount: autorización de acceso ──
    public function mount()
    {
        $this->authorize('viewAny', Palomar::class);
    }

    // ── Consulta con caché ──
    #[Computed]
    public function palomares()
    {
        $query = Palomar::withCount('palomas')
            ->orderBy('nombre');

        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('ubicacion', 'like', '%' . $this->search . '%');
        }

        return $query->paginate(15);
    }

    // ── ABRIR FORMULARIO DE ALTA ──
    public function crear()
    {
        try {
            $this->authorize('create', Palomar::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
        $this->dispatch('abrir-modal-palomar');
    }

    // ── ABRIR FORMULARIO DE EDICIÓN ──
    public function abrirEditar(int $palomarId)
    {
        $palomar = Palomar::findOrFail($palomarId);

        try {
            $this->authorize('update', $palomar);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formPalomarId = $palomar->id;
        $this->formNombre = $palomar->nombre;
        $this->formUbicacion = $palomar->ubicacion ?? '';
        $this->formCapacidadMaxima = $palomar->capacidad_maxima;
        $this->formObservaciones = $palomar->observaciones ?? '';
        $this->formActivo = (bool) $palomar->activo;
        $this->showForm = true;
        $this->dispatch('abrir-modal-palomar');
    }

    // ── CERRAR FORMULARIO ──
    public function cerrarForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
        $this->dispatch('cerrar-modal-palomar');
    }

    // ── GUARDAR (create o update) ──
    public function guardar()
    {
        try {
            if ($this->formTipo === 'create') {
                $this->authorize('create', Palomar::class);
            } else {
                $palomar = Palomar::findOrFail($this->formPalomarId);
                $this->authorize('update', $palomar);
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
            $data = $this->datosValidados();

            if ($this->formTipo === 'create') {
                Palomar::create($data);
                $this->successMsg = 'Palomar creado correctamente.';
            } else {
                $palomar = Palomar::findOrFail($this->formPalomarId);
                $palomar->update($data);
                $this->successMsg = 'Palomar actualizado correctamente.';
            }

            $this->showForm = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // ── CONFIRMAR ELIMINACIÓN (SweetAlert2) ──
    public function confirmarEliminacion(int $palomarId)
    {
        $palomar = Palomar::withCount('palomas')->findOrFail($palomarId);

        try {
            $this->authorize('delete', $palomar);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        // Verificación temprana — no llega al SweetAlert
        if ($palomar->palomas_count > 0) {
            $this->errorMsg = 'No se puede eliminar: el palomar tiene palomas asociadas.';
            return;
        }

        $this->confirmDeleteId = $palomarId;
    }

    // ── EJECUTAR ELIMINACIÓN ──
    public function ejecutarEliminacion()
    {
        $this->loading = true;
        try {
            $palomar = Palomar::findOrFail($this->confirmDeleteId);
            $palomar->delete();
            $this->successMsg = 'Palomar eliminado correctamente.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->confirmDeleteId = null;
        }
    }

    // ── DATOS VALIDADOS (array limpio) ──
    protected function datosValidados(): array
    {
        return [
            'nombre' => $this->formNombre,
            'ubicacion' => $this->formUbicacion ?: null,
            'capacidad_maxima' => $this->formCapacidadMaxima ?: null,
            'observaciones' => $this->formObservaciones ?: null,
            'activo' => (bool) $this->formActivo,
        ];
    }

    // ── RESET DE CAMPOS ──
    protected function resetForm(): void
    {
        $this->formTipo = 'create';
        $this->formPalomarId = null;
        $this->formNombre = '';
        $this->formUbicacion = '';
        $this->formCapacidadMaxima = null;
        $this->formObservaciones = '';
        $this->formActivo = false;
    }

    // ── REGLAS DE VALIDACIÓN ──
    protected function reglasValidacion(): array
    {
        $unique = $this->formTipo === 'create'
            ? 'unique:palomares,nombre'
            : 'unique:palomares,nombre,' . $this->formPalomarId;

        return [
            'formNombre' => 'required|string|max:255|' . $unique,
            'formUbicacion' => 'nullable|string|max:255',
            'formCapacidadMaxima' => 'nullable|integer|min:0',
            'formObservaciones' => 'nullable|string',
            'formActivo' => 'boolean',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formNombre.required' => 'El nombre es obligatorio.',
            'formNombre.unique' => 'Ya existe un palomar con ese nombre.',
            'formCapacidadMaxima.integer' => 'La capacidad debe ser un número entero.',
            'formCapacidadMaxima.min' => 'La capacidad no puede ser negativa.',
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
        return view('livewire.palomares', [
            'palomares' => $this->palomares(),
        ]);
    }
}
