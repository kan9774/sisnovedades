<?php

namespace App\Livewire;

use App\Models\Paloma;
use App\Models\Palomar;
use App\Models\EstadoPaloma;
use App\Models\HistorialPaloma;
use App\Traits\UsesBootstrapPagination;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Palomas extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    // ── Estado de búsqueda ──
    public $search = '';

    // ── Estado del formulario ──
    public $showForm = false;
    public $formTipo = 'create';
    public $formId = null;

    // ── Datos del formulario ──
    public $formPalomarId = '';
    public $formAnilla = '';
    public $formFechaNacimiento = '';
    public $formSexo = 'desconocido';
    public $formEstadoId = '';
    public $formEstadoSanitario = 'Bien';
    public $formObservaciones = '';

    // ── Catálogos ──
    public $palomares = [];
    public $estados = [];

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    // ── Confirmación de eliminación ──
    public $confirmDeleteId = null;

    // ── mount: autorización de acceso + cargar catálogos ──
    public function mount()
    {

        $this->authorize('viewAny', Paloma::class);
        $this->cargarCatalogos();
    }

    // ── Cargar catálogos una vez ──
    protected function cargarCatalogos(): void
    {
        $this->palomares = Palomar::where('activo', true)->get(['id', 'nombre']);
        $this->estados = EstadoPaloma::where('activo', true)->get(['id', 'nombre']);
    }

    // ── Consulta con caché ──
    #[Computed]
    public function palomas()
    {
        $query = Paloma::with(['palomar', 'estado'])
            ->orderBy('anilla');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('anilla', 'like', '%' . $this->search . '%')
                  ->orWhereHas('palomar', fn($pq) => $pq->where('nombre', 'like', '%' . $this->search . '%'));
            });
        }

        return $query->paginate(15);
    }

    // ── ABRIR FORMULARIO DE ALTA ──
    public function crear()
    {
        try {
            $this->authorize('create', Paloma::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
        $this->dispatch('abrir-modal-paloma');
    }

    // ── ABRIR FORMULARIO DE EDICIÓN ──
    public function abrirEditar(int $palomaId)
    {
        $paloma = Paloma::findOrFail($palomaId);

        try {
            $this->authorize('update', $paloma);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formId = $paloma->id;
        $this->formPalomarId = $paloma->palomar_id;
        $this->formAnilla = $paloma->anilla;
        $this->formFechaNacimiento = optional($paloma->fecha_nacimiento)->format('Y-m-d');
        $this->formSexo = $paloma->sexo;
        $this->formEstadoId = $paloma->estado_id;
        $this->formEstadoSanitario = $paloma->estado_sanitario ?? 'Bien';
        $this->formObservaciones = $paloma->observaciones ?? '';

        $this->showForm = true;
        $this->dispatch('abrir-modal-paloma');
    }

    // ── CERRAR FORMULARIO ──
    public function cerrarForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
        $this->dispatch('cerrar-modal-paloma');
    }

    // ── GUARDAR (create o update) ──
    public function guardar()
    {
        try {
            if ($this->formTipo === 'create') {
                $this->authorize('create', Paloma::class);
            } else {
                $paloma = Paloma::findOrFail($this->formId);
                $this->authorize('update', $paloma);
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
                $paloma = Paloma::create($data);

                // Registrar historial inicial (nacimiento)
                $paloma->historial()->create([
                    'evento' => 'creacion',
                    'estado_nuevo_id' => $data['estado_id'],
                    'fecha_evento' => now(),
                    'user_id' => auth()->id(),
                    'observaciones' => 'Registro inicial de la paloma',
                ]);

                $this->successMsg = 'Paloma creada correctamente.';
            } else {
                $paloma = Paloma::findOrFail($this->formId);

                $estadoAnteriorId = $paloma->estado_id;
                $paloma->update($data);

                // Registrar historial si cambió el estado
                if ($estadoAnteriorId != $data['estado_id']) {
                    $estadoNuevo = EstadoPaloma::find($data['estado_id']);
                    HistorialPaloma::create([
                        'paloma_id' => $paloma->id,
                        'evento' => $this->determinarEvento($estadoNuevo->nombre),
                        'estado_anterior_id' => $estadoAnteriorId,
                        'estado_nuevo_id' => $data['estado_id'],
                        'fecha_evento' => now(),
                        'user_id' => auth()->id(),
                        'observaciones' => 'Cambio de estado',
                    ]);
                }

                $this->successMsg = 'Paloma actualizada correctamente.';
            }

            $this->showForm = false;
            $this->resetForm();
            $this->cargarCatalogos();
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // ── CONFIRMAR ELIMINACIÓN (SweetAlert2) ──
    public function confirmarEliminacion(int $palomaId)
    {
        $paloma = Paloma::withCount('vuelos')->findOrFail($palomaId);

        try {
            $this->authorize('delete', $paloma);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        // Verificación temprana — no llega al SweetAlert
        if ($paloma->vuelos_count > 0) {
            $this->errorMsg = 'No se puede eliminar: la paloma tiene vuelos registrados.';
            return;
        }

        $this->confirmDeleteId = $palomaId;
    }

    // ── EJECUTAR ELIMINACIÓN ──
    public function ejecutarEliminacion()
    {
        $this->loading = true;
        try {
            $paloma = Paloma::findOrFail($this->confirmDeleteId);
            $paloma->delete();
            $this->successMsg = 'Paloma eliminada correctamente.';
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
            'palomar_id' => $this->formPalomarId,
            'anilla' => $this->formAnilla,
            'fecha_nacimiento' => $this->formFechaNacimiento ?: null,
            'sexo' => $this->formSexo,
            'estado_id' => $this->formEstadoId,
            'estado_sanitario' => $this->formEstadoSanitario,
            'observaciones' => $this->formObservaciones ?: null,
        ];
    }

    // ── RESET DE CAMPOS ──
    protected function resetForm(): void
    {
        $this->formTipo = 'create';
        $this->formId = null;
        $this->formPalomarId = '';
        $this->formAnilla = '';
        $this->formFechaNacimiento = '';
        $this->formSexo = 'desconocido';
        $this->formEstadoId = '';
        $this->formEstadoSanitario = 'Bien';
        $this->formObservaciones = '';
    }

    // ── REGLAS DE VALIDACIÓN ──
    protected function reglasValidacion(): array
    {
        $uniqueAnilla = $this->formTipo === 'create'
            ? 'unique:palomas,anilla'
            : 'unique:palomas,anilla,' . $this->formId;

        return [
            'formPalomarId' => 'required|exists:palomares,id',
            'formAnilla' => 'required|string|max:50|' . $uniqueAnilla,
            'formFechaNacimiento' => $this->formTipo === 'create'
                ? 'nullable|date|before:today'
                : 'nullable|date',
            'formSexo' => 'required|in:macho,hembra,desconocido',
            'formEstadoId' => 'required|exists:estados_paloma,id',
            'formEstadoSanitario' => 'required|in:Bien,Enferma',
            'formObservaciones' => 'nullable|string',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formPalomarId.required' => 'El palomar es obligatorio.',
            'formAnilla.required' => 'La anilla es obligatoria.',
            'formAnilla.unique' => 'Ya existe una paloma con esa anilla.',
            'formSexo.required' => 'El sexo es obligatorio.',
            'formSexo.in' => 'El sexo debe ser macho, hembra o desconocido.',
            'formEstadoId.required' => 'El estado es obligatorio.',
            'formEstadoSanitario.required' => 'El estado sanitario es obligatorio.',
            'formEstadoSanitario.in' => 'El estado sanitario debe ser Bien o Enferma.',
            'formFechaNacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        ];
    }

    // ── REACTIVO: al cambiar búsqueda, resetear página ──
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // ── DETERMINAR EVENTO PARA HISTORIAL ──
    protected function determinarEvento(string $estadoNombre): string
    {
        $eventos = [
            'Vendida' => 'venta',
            'En préstamo' => 'prestamo',
            'Baja' => 'muerte',
            'Ausente' => 'ausente',
        ];

        return $eventos[$estadoNombre] ?? 'cambio_estado';
    }

    // ── RENDER ──
    public function render()
    {
        return view('livewire.palomas', [
            'palomas' => $this->palomas(),
        ]);
    }
}
