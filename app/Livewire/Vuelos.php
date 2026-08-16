<?php

namespace App\Livewire;

use App\Models\Vuelo;
use App\Models\Paloma;
use App\Models\EstadoPaloma;
use App\Models\HistorialPaloma;
use App\Traits\UsesBootstrapPagination;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Vuelos extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    // ── Estado de búsqueda ──
    public $search = '';
    public $palomaFilter = '';

    // ── Estado del formulario ──
    public $showForm = false;
    public $formFormTipo = 'create';
    public $formVueloId = null;

    // ── Datos generales del vuelo ──
    public $formFecha = '';
    public $formVueloTipo = '';
    public $formPuntoLiberacion = '';
    public $formHoraLiberacion = '';
    public $formCondicionesClimaticas = '';
    public $formObservaciones = '';

    // ── Palomas participantes ──
    public $selectedPalomaIds = [];
    public $palomasDatos = [];

    // ── Catálogos ──
    public $palomasActivas = [];

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    // ── Confirmación de eliminación ──
    public $confirmDeleteId = null;

    // ── Vuelo para edición (estado finalizado check en blade) ──
    public $vuelo = null;

    // ── mount: autorización de acceso + cargar catálogos ──
    public function mount()
    {
        $this->authorize('viewAny', Vuelo::class);
        $this->cargarCatalogos();
    }

    // ── Cargar catálogos una vez ──
    protected function cargarCatalogos(): void
    {
        $this->palomasActivas = Paloma::whereHas('estado', fn($q) => $q->where('nombre', 'Activa'))
            ->with('estado')
            ->orderBy('anilla')
            ->get(['id', 'anilla', 'nombre', 'estado_id']);
    }

    // ── Consulta con caché ──
    #[Computed]
    public function vuelos()
    {
        $query = Vuelo::with('palomas')
            ->orderBy('fecha', 'desc');

        if ($this->palomaFilter) {
            $query->whereHas('palomas', fn($q) => $q->where('palomas.id', $this->palomaFilter));
        }

        return $query->paginate(15);
    }

    // ── ABRIR FORMULARIO DE ALTA ──
    public function crear()
    {
        try {
            $this->authorize('create', Vuelo::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetForm();
        $this->formFormTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
    }

    // ── ABRIR FORMULARIO DE EDICIÓN ──
    public function abrirEditar(int $vueloId)
    {
        $vuelo = Vuelo::with('palomas')->findOrFail($vueloId);

        try {
            $this->authorize('update', $vuelo);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetErrorBag();
        $this->formFormTipo = 'edit';
        $this->formVueloId = $vuelo->id;
        $this->formFecha = $vuelo->fecha->format('Y-m-d');
        $this->formVueloTipo = $vuelo->tipo;
        $this->formPuntoLiberacion = $vuelo->punto_liberacion ?? '';
        $this->formHoraLiberacion = optional($vuelo->hora_liberacion)->format('H:i');
        $this->formCondicionesClimaticas = $vuelo->condiciones_climaticas ?? '';
        $this->formObservaciones = $vuelo->observaciones ?? '';

        $this->selectedPalomaIds = $vuelo->palomas->pluck('id')->keyBy('id')->toArray();

        $this->palomasDatos = [];
        foreach ($vuelo->palomas as $paloma) {
            $this->palomasDatos[$paloma->id] = [
                'anilla_competicion' => $paloma->pivot->anilla_competicion ?? '',
            ];
        }

        $this->vuelo = $vuelo;

        $this->showForm = true;
    }

    // ── CERRAR FORMULARIO ──
    public function cerrarForm()
    {
        $this->showForm = false;
        $this->vuelo = null;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
    }

    // ── GUARDAR (create o update) ──
    public function guardar()
    {
        try {
            if ($this->formFormTipo === 'create') {
                $this->authorize('create', Vuelo::class);
            } else {
                $vuelo = Vuelo::findOrFail($this->formVueloId);
                $this->authorize('update', $vuelo);
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

            if ($this->formFormTipo === 'create') {
                $vuelo = Vuelo::create([
                    'fecha' => $data['fecha'],
                    'tipo' => $data['tipo'],
                    'punto_liberacion' => $data['punto_liberacion'] ?? null,
                    'hora_liberacion' => $data['hora_liberacion'] ?? null,
                    'condiciones_climaticas' => $data['condiciones_climaticas'] ?? null,
                    'observaciones' => $data['observaciones'] ?? null,
                    'estado' => 'en_curso',
                ]);

                $palomas = Paloma::whereIn('id', $data['selected_paloma_ids'])->get()->keyBy('id');

                $pivotData = [];
                foreach ($data['selected_paloma_ids'] as $palomaId) {
                    $pivotData[$palomaId] = [
                        'estado_anterior_id' => $palomas[$palomaId]->estado_id,
                        'anilla_competicion' => $data['palomas_datos'][$palomaId]['anilla_competicion'] ?? null,
                    ];
                }
                $vuelo->palomas()->attach($pivotData);

                $this->marcarPalomasEnVuelo($palomas, $data['tipo']);

                $this->successMsg = 'Vuelo registrado. Las palomas fueron marcadas como en vuelo.';
            } else {
                $vuelo = Vuelo::findOrFail($this->formVueloId);

                $vuelo->update([
                    'fecha' => $data['fecha'],
                    'tipo' => $data['tipo'],
                    'punto_liberacion' => $data['punto_liberacion'] ?? null,
                    'hora_liberacion' => $data['hora_liberacion'] ?? null,
                    'condiciones_climaticas' => $data['condiciones_climaticas'] ?? null,
                    'observaciones' => $data['observaciones'] ?? null,
                ]);

                if ($vuelo->estado === 'en_curso') {
                    $idsNuevos = collect($data['selected_paloma_ids']);
                    $idsActuales = $vuelo->palomas->pluck('id');

                    $idsAAgregar = $idsNuevos->diff($idsActuales);
                    $idsARemover = $idsActuales->diff($idsNuevos);
                    $idsAMantener = $idsNuevos->intersect($idsActuales);

                    if ($idsARemover->isNotEmpty()) {
                        $palomasARemover = Paloma::whereIn('id', $idsARemover)->get()->keyBy('id');

                        foreach ($idsARemover as $palomaId) {
                            $pivotExistente = $vuelo->palomas->firstWhere('id', $palomaId)->pivot;
                            $estadoAnteriorId = $pivotExistente->estado_anterior_id;
                            $paloma = $palomasARemover[$palomaId];

                            if ($estadoAnteriorId) {
                                $estadoActualId = $paloma->estado_id;
                                $paloma->update(['estado_id' => $estadoAnteriorId]);

                                HistorialPaloma::create([
                                    'paloma_id' => $paloma->id,
                                    'evento' => 'cambio_estado',
                                    'estado_anterior_id' => $estadoActualId,
                                    'estado_nuevo_id' => $estadoAnteriorId,
                                    'fecha_evento' => now(),
                                    'user_id' => auth()->id(),
                                    'observaciones' => 'Removida del vuelo #' . $vuelo->id . ' antes de finalizar',
                                ]);
                            }
                        }

                        $vuelo->palomas()->detach($idsARemover->all());
                    }

                    if ($idsAAgregar->isNotEmpty()) {
                        $palomasAAgregar = Paloma::whereIn('id', $idsAAgregar)->get()->keyBy('id');

                        $pivotData = [];
                        foreach ($idsAAgregar as $palomaId) {
                            $pivotData[$palomaId] = [
                                'estado_anterior_id' => $palomasAAgregar[$palomaId]->estado_id,
                                'anilla_competicion' => $data['palomas_datos'][$palomaId]['anilla_competicion'] ?? null,
                            ];
                        }
                        $vuelo->palomas()->attach($pivotData);

                        $this->marcarPalomasEnVuelo($palomasAAgregar, $data['tipo']);
                    }

                    foreach ($idsAMantener as $palomaId) {
                        $vuelo->palomas()->updateExistingPivot($palomaId, [
                            'anilla_competicion' => $data['palomas_datos'][$palomaId]['anilla_competicion'] ?? null,
                        ]);
                    }
                }

                $this->successMsg = 'Vuelo actualizado correctamente.';
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

    // ── CONFIRMAR ELIMINACIÓN ──
    public function confirmarEliminacion(int $vueloId)
    {
        $vuelo = Vuelo::findOrFail($vueloId);

        try {
            $this->authorize('delete', $vuelo);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        if ($vuelo->palomas()->count() > 0) {
            $this->errorMsg = 'No se puede eliminar: el vuelo tiene palomas registradas.';
            return;
        }

        $this->confirmDeleteId = $vueloId;
    }

    // ── EJECUTAR ELIMINACIÓN ──
    public function ejecutarEliminacion()
    {
        $this->loading = true;
        try {
            $vuelo = Vuelo::findOrFail($this->confirmDeleteId);
            $vuelo->palomas()->detach();
            $vuelo->delete();
            $this->successMsg = 'Vuelo eliminado correctamente.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->confirmDeleteId = null;
        }
    }

    // ── MARCAR PALOMAS EN VUELO ──
    protected function marcarPalomasEnVuelo($palomas, string $tipo): void
    {
        $nombreEstado = $tipo === 'competicion' ? 'En competición' : 'En vareo';

        $estado = EstadoPaloma::where('nombre', $nombreEstado)->first();

        if (!$estado) {
            $estado = EstadoPaloma::create([
                'nombre' => $nombreEstado,
                'color' => $tipo === 'competicion' ? '#ffc107' : '#17a2b8',
                'activo' => true,
            ]);
        }

        foreach ($palomas as $paloma) {
            $estadoAnteriorId = $paloma->estado_id;
            $paloma->update(['estado_id' => $estado->id]);

            HistorialPaloma::create([
                'paloma_id' => $paloma->id,
                'evento' => 'cambio_estado',
                'estado_anterior_id' => $estadoAnteriorId,
                'estado_nuevo_id' => $estado->id,
                'fecha_evento' => now(),
                'user_id' => auth()->id(),
                'observaciones' => 'Paloma enviada a ' . strtolower($nombreEstado),
            ]);
        }
    }

    // ── CALCULAR TIEMPO Y VELOCIDAD ──
    protected function calcularTiempoYVelocidad(?string $horaLiberacion, ?string $horaLlegada, ?float $distanciaKm): array
    {
        if (!$horaLiberacion || !$horaLlegada) {
            return ['tiempo_vuelo' => null, 'velocidad_media' => null];
        }

        $liberacion = \Carbon\Carbon::createFromFormat('H:i', $horaLiberacion);
        $llegada = \Carbon\Carbon::createFromFormat('H:i', $horaLlegada);

        if ($llegada->lessThan($liberacion)) {
            $llegada->addDay();
        }

        $diff = $liberacion->diff($llegada);
        $tiempoVuelo = $diff->format('%H:%I:%S');

        $velocidadMedia = null;
        if ($distanciaKm) {
            $horasTotales = $diff->h + ($diff->i / 60) + ($diff->s / 3600);
            if ($horasTotales > 0) {
                $velocidadMedia = round($distanciaKm / $horasTotales, 2);
            }
        }

        return ['tiempo_vuelo' => $tiempoVuelo, 'velocidad_media' => $velocidadMedia];
    }

    // ── REACTIVO: al cambiar búsqueda, resetear página ──
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPalomaFilter()
    {
        $this->resetPage();
    }

    // ── TOGGLE PALOMA EN SELECCIÓN ──
    public function togglePaloma(int $palomaId): void
    {
        if (isset($this->selectedPalomaIds[$palomaId])) {
            unset($this->selectedPalomaIds[$palomaId]);
            unset($this->palomasDatos[$palomaId]);
        } else {
            $this->selectedPalomaIds[$palomaId] = $palomaId;
        }
    }

    // ── DATOS VALIDADOS (array limpio) ──
    protected function datosValidados(): array
    {
        return [
            'fecha' => $this->formFecha,
            'tipo' => $this->formVueloTipo,
            'punto_liberacion' => $this->formPuntoLiberacion ?: null,
            'hora_liberacion' => $this->formHoraLiberacion ?: null,
            'condiciones_climaticas' => $this->formCondicionesClimaticas ?: null,
            'observaciones' => $this->formObservaciones ?: null,
            'selected_paloma_ids' => $this->selectedPalomaIds,
            'palomas_datos' => $this->buildPalomasDatos(),
        ];
    }

    protected function buildPalomasDatos(): array
    {
        $datos = [];
        foreach ($this->selectedPalomaIds as $palomaId) {
            $datos[$palomaId] = [
                'anilla_competicion' => $this->palomasDatos[$palomaId]['anilla_competicion'] ?? null,
            ];
        }
        return $datos;
    }

    // ── RESET DE CAMPOS ──
    protected function resetForm(): void
    {
        $this->formFormTipo = 'create';
        $this->formVueloId = null;
        $this->formFecha = date('Y-m-d');
        $this->formVueloTipo = '';
        $this->formPuntoLiberacion = '';
        $this->formHoraLiberacion = '';
        $this->formCondicionesClimaticas = '';
        $this->formObservaciones = '';
        $this->selectedPalomaIds = [];
        $this->palomasDatos = [];
    }

    // ── REGLAS DE VALIDACIÓN ──
    protected function reglasValidacion(): array
    {
        return [
            'formFecha' => 'required|date|before_or_equal:today',
            'formVueloTipo' => 'required|in:entrenamiento,competicion',
            'formPuntoLiberacion' => 'nullable|string|max:255',
            'formHoraLiberacion' => 'nullable|date_format:H:i',
            'formCondicionesClimaticas' => 'nullable|string',
            'formObservaciones' => 'nullable|string',
            'selectedPalomaIds' => 'required|array|min:1',
            'selectedPalomaIds.*' => 'exists:palomas,id',
            'palomasDatos' => 'nullable|array',
            'palomasDatos.*.anilla_competicion' => 'nullable|string|max:50',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formFecha.required' => 'La fecha es obligatoria.',
            'formFecha.before_or_equal' => 'La fecha no puede ser posterior a hoy.',
            'formVueloTipo.required' => 'El tipo de vuelo es obligatorio.',
            'formVueloTipo.in' => 'El tipo debe ser: entrenamiento o competición.',
            'selectedPalomaIds.required' => 'Debe seleccionar al menos una paloma.',
            'selectedPalomaIds.min' => 'Debe seleccionar al menos una paloma.',
        ];
    }

    // ── RENDER ──
    public function render()
    {
        return view('livewire.vuelos.index', [
            'vuelos' => $this->vuelos(),
            'palomasActivas' => $this->palomasActivas,
            'vuelo' => $this->vuelo,
        ]);
    }
}
