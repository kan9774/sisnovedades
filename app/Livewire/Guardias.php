<?php

namespace App\Livewire;

use App\Models\Guard;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Traits\UsesBootstrapPagination;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Guardias extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    // ── Estado de búsqueda ──
    public $search = '';

    // ── Estado de vista: activos o papelera ──
    public $viewMode = 'activos';

    // ── Estado del formulario ──
    public $showForm = false;
    public $formTipo = 'create';
    public $formGuardiaId = null;

    // ── Form campos ──
    public $formDate = '';
    public $formDateDisplay = '';
    public $formCaptainId = null;
    public $formOficerId = null;
    public $formEscribienteId = null;
    public $formNotes = '';

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;
    public $confirmDeleteId = null;

    // ── Eliminación permanente (papelera) ──
    public $guardiaAEliminarId = null;

    // ── mount: autorización de acceso ──
    public function mount()
    {
        $this->authorize('viewAny', Guard::class);
    }

    // ── Navegación entre vistas ──
    public function verPapelera()
    {
        $this->viewMode = 'papelera';
        $this->resetPage();
    }

    public function verActivos()
    {
        $this->viewMode = 'activos';
        $this->resetPage();
    }

    // ── Consulta con caché ──
    #[Computed]
    public function guardias()
    {
        if ($this->viewMode === 'papelera') {
            $query = Guard::onlyTrashed()
                ->with(['capitan.grado', 'oficial.grado', 'escribiente.grado'])
                ->withCount('novedades')
                ->orderByDesc('date');
        } else {
            $query = Guard::with(['capitan.grado', 'oficial.grado', 'escribiente.grado'])
                ->withCount('novedades')
                ->orderByDesc('date');
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('capitan', fn($cq) => $cq->where('name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                  ->orWhereHas('oficial', fn($oq) => $oq->where('name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                  ->orWhereHas('escribiente', fn($eq) => $eq->where('name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                  ->orWhere('date', 'like', "%{$search}%");
            });
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function catalogos()
    {
        $capitanes = User::whereHas('roles', fn($q) => $q->where('name', 'capitan_de_servicio'))
            ->with('grado')
            ->get()
            ->reject(fn($u) => $u->isSuperAdmin())
            ->values();

        $oficiales = User::whereHas('roles', fn($q) => $q->where('name', 'oficial_de_dia'))->with('grado')->get();

        $escribientes = User::whereHas('roles', fn($q) => $q->where('name', 'escribiente'))->with('grado')->get();

        $tiposVehiculo = TipoVehiculo::where('activo', true)->orderBy('nombre')->get();

        return [
            'capitanes' => $capitanes,
            'oficiales' => $oficiales,
            'escribientes' => $escribientes,
            'tiposVehiculo' => $tiposVehiculo,
        ];
    }

    // ── REACTIVO: al cambiar búsqueda, forzar reset page ──
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // ── ABRIR FORMULARIO DE ALTA ──
    public function crear()
    {
        try {
            $this->authorize('create', Guard::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetForm();
        $this->formTipo = 'create';
        $this->formDate = today()->format('Y-m-d');

        // Si el usuario autenticado es escribiente, forzarlo como escribiente
        if (Auth::user()->isEscribiente()) {
            $this->formEscribienteId = Auth::id();
        }

        $this->showForm = true;
        $this->resetErrorBag();
    }

    // ── ABRIR FORMULARIO DE EDICIÓN ──
    public function abrirEditar(int $guardiaId)
    {
        $guardia = Guard::with('escribiente')->findOrFail($guardiaId);

        try {
            $this->authorize('update', $guardia);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formGuardiaId = $guardia->id;

        $this->formCaptainId = $guardia->captain_id;
        $this->formOficerId = $guardia->oficer_id;
        $this->formEscribienteId = $guardia->escribiente->first()?->id;
        $this->formNotes = $guardia->notes;
        $this->formDateDisplay = $guardia->date->format('d/m/Y');
        $this->formDate = $guardia->date->format('Y-m-d');

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
                $this->authorize('create', Guard::class);
            } else {
                $guardia = Guard::findOrFail($this->formGuardiaId);
                $this->authorize('update', $guardia);
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
                $this->crearGuardia();
            } else {
                $this->actualizarGuardia();
            }

            $this->successMsg = $this->formTipo === 'create'
                ? 'Guardia creada exitosamente.'
                : 'Guardia actualizada correctamente.';

            $this->showForm = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    protected function crearGuardia()
    {
        $data = $this->datosValidados();

        // Escribiente forzado: si el usuario autenticado es escribiente,
        // siempre queda él/ella asignado, sin importar qué se haya enviado.
        $escribienteId = Auth::user()->isEscribiente()
            ? Auth::id()
            : $data['escribiente_id'];

        $guardia = Guard::create([
            'date' => $data['date'],
            'captain_id' => $data['captain_id'],
            'oficer_id' => $data['oficer_id'],
            'status' => 'open',
            'notes' => $data['notes'] ?? null,
        ]);
        $guardia->escribiente()->attach($escribienteId);
    }

    protected function actualizarGuardia()
    {
        $guardia = Guard::findOrFail($this->formGuardiaId);
        $data = $this->datosValidados();

        $escribientesAnterioresIds = $guardia->escribiente()->pluck('users.id')->all();
        $escribienteNuevoId = (int) $data['escribiente_id'];

        $guardia->update([
            'captain_id' => $data['captain_id'],
            'oficer_id' => $data['oficer_id'],
            'notes' => $data['notes'] ?? null,
        ]);

        if ($escribientesAnterioresIds !== [$escribienteNuevoId]) {
            $guardia->escribiente()->sync([$escribienteNuevoId]);

            $nombresAnteriores = User::whereIn('id', $escribientesAnterioresIds)
                ->with('grado')
                ->get()
                ->map(fn($u) => "{$u->grade} {$u->name} {$u->last_name}")
                ->implode(', ') ?: '—';
            $escribienteNuevo = User::find($escribienteNuevoId);
            $nombreNuevoStr = $escribienteNuevo
                ? "{$escribienteNuevo->grade} {$escribienteNuevo->name} {$escribienteNuevo->last_name}"
                : '—';

            activity('Guardias')
                ->causedBy(Auth::user())
                ->performedOn($guardia)
                ->withProperties([
                    'old' => ['escribiente' => $nombresAnteriores],
                    'attributes' => ['escribiente' => $nombreNuevoStr],
                ])
                ->log('Cambio de escribiente de la guardia');
        }
    }

    // ── DATOS VALIDADOS (array limpio) ──
    protected function datosValidados(): array
    {
        return [
            'date' => $this->formDate,
            'captain_id' => $this->formCaptainId,
            'oficer_id' => $this->formOficerId,
            'escribiente_id' => $this->formEscribienteId,
            'notes' => $this->formNotes,
        ];
    }

    // ── ELIMINAR (confirmación + ejecución separada) ──
    public function confirmarEliminacion(int $guardiaId)
    {
        $guardia = Guard::findOrFail($guardiaId);

        try {
            $this->authorize('delete', $guardia);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        // No eliminar si está abierta
        if ($guardia->status === 'open') {
            $this->errorMsg = 'No se puede eliminar una guardia abierta. Ciérrala primero.';
            return;
        }

        $this->confirmDeleteId = $guardiaId;
    }

    public function ejecutarEliminacion()
    {
        $this->loading = true;
        try {
            $guardia = Guard::findOrFail($this->confirmDeleteId);
            $guardia->delete();
            $this->successMsg = 'Guardia eliminada correctamente. Puedes restaurarla desde la papelera.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->confirmDeleteId = null;
        }
    }

    // ── PAPELERA: Restaurar ──
    public function restaurar($id)
    {
        $guardia = Guard::onlyTrashed()->findOrFail($id);

        try {
            $this->authorize('restore', $guardia);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $guardia->restore();
        $this->successMsg = 'Guardia restaurada correctamente.';
    }

    // ── PAPELERA: Confirmar eliminación permanente ──
    public function confirmarEliminacionPermanente($id)
    {
        $guardia = Guard::onlyTrashed()->findOrFail($id);

        try {
            $this->authorize('forceDelete', $guardia);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->guardiaAEliminarId = $id;
    }

    // ── PAPELERA: Ejecutar eliminación permanente (con cascada) ──
    public function ejecutarEliminacionPermanente()
    {
        $this->loading = true;
        try {
            $guardia = Guard::onlyTrashed()->findOrFail($this->guardiaAEliminarId);

            DB::transaction(function () use ($guardia) {
                $guardia->load('novedades.adjuntos', 'novedades.logs', 'salidasVehiculos');

                foreach ($guardia->novedades as $novedad) {
                    foreach ($novedad->adjuntos as $adjunto) {
                        Storage::disk('guardias')->delete($adjunto->file_path);
                        $adjunto->delete();
                    }

                    $novedad->delete();
                }

                $guardia->salidasVehiculos()->delete();
                $guardia->escribiente()->detach();
                $guardia->forceDelete();
            });

            $this->successMsg = 'Guardia, novedades, adjuntos y salidas de vehículo eliminados permanentemente.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar permanentemente: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->guardiaAEliminarId = null;
        }
    }

    // ── RESET DE CAMPOS ──
    protected function resetForm(): void
    {
        $this->formTipo = 'create';
        $this->formGuardiaId = null;
        $this->formDate = '';
        $this->formDateDisplay = '';
        $this->formCaptainId = '';
        $this->formOficerId = '';
        $this->formEscribienteId = '';
        $this->formNotes = '';
    }

    // ── REGLAS DE VALIDACIÓN ──
    protected function reglasValidacion(): array
    {
        $uniqueDate = $this->formTipo === 'create'
            ? 'unique:guards,date'
            : 'unique:guards,date,' . $this->formGuardiaId;

        return [
            'formDate' => 'required|date|' . $uniqueDate,
            'formCaptainId' => 'required|exists:users,id',
            'formOficerId' => 'required|exists:users,id',
            'formEscribienteId' => 'required|exists:users,id',
            'formNotes' => 'nullable|string',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formDate.required' => 'La fecha es obligatoria.',
            'formDate.unique' => 'Ya existe una guardia para esa fecha.',
            'formCaptainId.required' => 'El capitán es obligatorio.',
            'formCaptainId.exists' => 'El capitán seleccionado no existe.',
            'formOficerId.required' => 'El oficial es obligatorio.',
            'formOficerId.exists' => 'El oficial seleccionado no existe.',
            'formEscribienteId.required' => 'El escribiente es obligatorio.',
            'formEscribienteId.exists' => 'El escribiente seleccionado no existe.',
        ];
    }

    // ── LIMPIAR FILTROS ──
    public function limpiarFiltros()
    {
        $this->search = '';
        $this->resetPage();
    }

    // ── RENDER ──
    public function render()
    {
        return view('livewire.guardias.index', [
            'guardias' => $this->guardias(),
            'catalogos' => $this->catalogos(),
        ]);
    }
}
