<?php

namespace App\Livewire;

use App\Models\Apoyo;
use App\Models\News;
use App\Models\Organismo;
use App\Models\TipoApoyo;
use App\Models\Unidad;
use App\Traits\UsesBootstrapPagination;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class Apoyos extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    public $search = '';
    public $filtroTipo = '';
    public $filtroEstado = '';

    public string $vistaActual = 'tabla';
    public int $mes = 0;
    public int $anio = 0;

    public ?string $diaSeleccionado = null;

    public $showForm = false;
    public $formTipo = 'create';
    public $formId = null;

    public $formTipoId = '';
    public $formOrganismoId = '';
    public $formDocumentoNovedadId = null;
    public $formDocumentoTexto = '';
    public $formDocumentoBusqueda = '';
    public $formDesde = '';
    public $formHasta = '';
    public $formPorDocumentoNovedadId = null;
    public $formPorDocumentoTexto = '';
    public $formPorDocumentoBusqueda = '';
    public $formUnidades = [];
    public $formEstado = 'pendiente';
    public $formDescripcion = '';

    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;
    public $justSaved = false;
    public $confirmDeleteId = null;

    #[Computed]
    public function apoyos()
    {
        $query = Apoyo::with(['tipo', 'organismo', 'unidades', 'registradoPor', 'cumplidoPor'])
            ->orderByDesc('apoyos.id');

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('organismo', fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhere('documento_texto', 'like', '%' . $this->search . '%')
                    ->orWhere('por_documento_texto', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filtroTipo !== '') {
            $query->where('tipo_id', $this->filtroTipo);
        }

        if ($this->filtroEstado !== '') {
            $query->where('estado', $this->filtroEstado);
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function tiposApoyo()
    {
        return TipoApoyo::orderBy('nombre')->get();
    }

    #[Computed]
    public function organismos()
    {
        return Organismo::orderBy('name')->get();
    }

    #[Computed]
    public function unidadesDisponibles()
    {
        return Unidad::where('activo', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function unidadesNombres()
    {
        return $this->unidadesDisponibles->pluck('nombre')->toArray();
    }

    #[Computed]
    public function unidadesMap()
    {
        return $this->unidadesDisponibles->pluck('nombre', 'id')->toArray();
    }

    public function mount()
    {
        $this->authorize('viewAny', Apoyo::class);
        $this->mes = (int) now()->month;
        $this->anio = (int) now()->year;
    }

    public function crear()
    {
        try {
            $this->authorize('create', Apoyo::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
        $this->dispatch('abrir-modal-apoyos');
    }

    public function abrirEditar(int $id)
    {
        $apoyo = Apoyo::with('unidades')->findOrFail($id);

        try {
            $this->authorize('update', $apoyo);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        // Si se abrió desde el reporte del día, cerrarlo para no apilar overlays.
        $this->diaSeleccionado = null;
        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formId = $apoyo->id;
        $this->formTipoId = $apoyo->tipo_id;
        $this->formOrganismoId = $apoyo->organismo_id;
        $this->formDocumentoNovedadId = $apoyo->documento_novedad_id;
        $this->formDocumentoTexto = $apoyo->documento_texto ?? '';
        $this->formDocumentoBusqueda = '';
        $this->formDesde = $apoyo->desde?->format('Y-m-d\TH:i');
        $this->formHasta = $apoyo->hasta?->format('Y-m-d\TH:i');
        $this->formPorDocumentoNovedadId = $apoyo->por_documento_novedad_id;
        $this->formPorDocumentoTexto = $apoyo->por_documento_texto ?? '';
        $this->formPorDocumentoBusqueda = '';
        $this->formUnidades = $apoyo->unidades->pluck('id')->toArray();
        $this->formEstado = $apoyo->estado;
        $this->formDescripcion = $apoyo->descripcion ?? '';
        $this->showForm = true;
        $this->dispatch('abrir-modal-apoyos');
    }

    public function cerrarForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
        $this->dispatch('cerrar-modal-apoyos');
    }

    public function buscarDocumento()
    {
        $this->formDocumentoNovedadId = null;
        $termino = trim($this->formDocumentoBusqueda);

        if (strlen($termino) < 2) {
            return;
        }

        $novedad = News::where('number', 'like', '%' . $termino . '%')
            ->orWhere('type', 'like', '%' . $termino . '%')
            ->first();

        if ($novedad) {
            $this->formDocumentoNovedadId = $novedad->id;
            $this->formDocumentoTexto = '';
        }
    }

    public function seleccionarDocumento($novedadId)
    {
        $novedad = News::find($novedadId);
        if ($novedad) {
            $this->formDocumentoNovedadId = $novedad->id;
            $this->formDocumentoTexto = '';
            $this->formDocumentoBusqueda = '';
        }
    }

    public function limpiarDocumento()
    {
        $this->formDocumentoNovedadId = null;
        $this->formDocumentoBusqueda = '';
    }

    public function buscarPorDocumento()
    {
        $this->formPorDocumentoNovedadId = null;
        $termino = trim($this->formPorDocumentoBusqueda);

        if (strlen($termino) < 2) {
            return;
        }

        $novedad = News::where('number', 'like', '%' . $termino . '%')
            ->orWhere('type', 'like', '%' . $termino . '%')
            ->first();

        if ($novedad) {
            $this->formPorDocumentoNovedadId = $novedad->id;
            $this->formPorDocumentoTexto = '';
        }
    }

    public function seleccionarPorDocumento($novedadId)
    {
        $novedad = News::find($novedadId);
        if ($novedad) {
            $this->formPorDocumentoNovedadId = $novedad->id;
            $this->formPorDocumentoTexto = '';
            $this->formPorDocumentoBusqueda = '';
        }
    }

    public function limpiarPorDocumento()
    {
        $this->formPorDocumentoNovedadId = null;
        $this->formPorDocumentoBusqueda = '';
    }

    public function guardar()
    {
        try {
            if ($this->formTipo === 'create') {
                $this->authorize('create', Apoyo::class);
            } else {
                $this->authorize('update', Apoyo::findOrFail($this->formId));
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
            $datos = [
                'tipo_id' => $this->formTipoId,
                'organismo_id' => $this->formOrganismoId,
                'documento_novedad_id' => $this->formDocumentoNovedadId,
                'documento_texto' => $this->formDocumentoNovedadId ? null : ($this->formDocumentoTexto ?: null),
                'desde' => $this->formDesde,
                'hasta' => $this->formHasta,
                'por_documento_novedad_id' => $this->formPorDocumentoNovedadId,
                'por_documento_texto' => $this->formPorDocumentoNovedadId ? null : ($this->formPorDocumentoTexto ?: null),
                'estado' => $this->formEstado,
                'descripcion' => $this->formDescripcion ?: null,
            ];

            if ($this->formTipo === 'create') {
                $datos['registrado_por_id'] = auth()->id();
                $apoyo = Apoyo::create($datos);
                $this->successMsg = 'Apoyo creado correctamente.';
            } else {
                $apoyo = Apoyo::findOrFail($this->formId);
                $estadoAnterior = $apoyo->estado;
                $apoyo->update($datos);

                if ($this->formEstado === 'cumplido' && $estadoAnterior !== 'cumplido') {
                    $apoyo->update([
                        'cumplido_por_id' => auth()->id(),
                        'cumplido_at' => now(),
                    ]);
                } elseif ($this->formEstado !== 'cumplido' && $estadoAnterior === 'cumplido') {
                    $apoyo->update([
                        'cumplido_por_id' => null,
                        'cumplido_at' => null,
                    ]);
                }

                $this->successMsg = 'Apoyo actualizado correctamente.';
            }

            $apoyo->unidades()->sync($this->formUnidades);

            $this->justSaved = true;
            $this->page = 1;
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function confirmDelete(int $id)
    {
        $this->confirmDeleteId = $id;
    }

    public function executeDelete()
    {
        $this->loading = true;
        try {
            $apoyo = Apoyo::findOrFail($this->confirmDeleteId);

            try {
                $this->authorize('delete', $apoyo);
            } catch (AuthorizationException $e) {
                $this->errorMsg = $e->getMessage();
                return;
            }

            $apoyo->delete();
            $this->successMsg = 'Apoyo eliminado correctamente.';
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
        $this->formId = null;
        $this->formTipoId = '';
        $this->formOrganismoId = '';
        $this->formDocumentoNovedadId = null;
        $this->formDocumentoTexto = '';
        $this->formDocumentoBusqueda = '';
        $this->formDesde = '';
        $this->formHasta = '';
        $this->formPorDocumentoNovedadId = null;
        $this->formPorDocumentoTexto = '';
        $this->formPorDocumentoBusqueda = '';
        $this->formUnidades = [];
        $this->formEstado = 'pendiente';
        $this->formDescripcion = '';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo()
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filtroTipo = '';
        $this->filtroEstado = '';
        $this->resetPage();
    }

    public function cambiarVista(string $vista): void
    {
        $this->vistaActual = $vista;
    }

    public function mesAnterior(): void
    {
        $fecha = Carbon::create($this->anio, $this->mes, 1)->subMonth();
        $this->mes = (int) $fecha->month;
        $this->anio = (int) $fecha->year;
    }

    public function mesSiguiente(): void
    {
        $fecha = Carbon::create($this->anio, $this->mes, 1)->addMonth();
        $this->mes = (int) $fecha->month;
        $this->anio = (int) $fecha->year;
    }

    public function irAHoy(): void
    {
        $this->mes = (int) now()->month;
        $this->anio = (int) now()->year;
    }

    public function seleccionarDia(string $fecha): void
    {
        if (self::parsearFechaDia($fecha) === null) {
            return;
        }

        $this->diaSeleccionado = $fecha;
        $this->errorMsg = '';
    }

    public function cerrarReporteDia(): void
    {
        $this->diaSeleccionado = null;
    }

    public function tituloDiaSeleccionado(): string
    {
        $dia = self::parsearFechaDia($this->diaSeleccionado);
        if ($dia === null) {
            return '';
        }

        $titulo = $dia->locale('es')->translatedFormat('l j \d\e F \d\e Y');

        return mb_strtoupper(mb_substr($titulo, 0, 1)) . mb_substr($titulo, 1);
    }

    #[Computed]
    public function apoyosDelDiaSeleccionado()
    {
        $dia = self::parsearFechaDia($this->diaSeleccionado);
        if ($dia === null) {
            return collect();
        }

        $dia = $dia->startOfDay();

        return $this->apoyosDelMes
            ->filter(function ($apoyo) use ($dia) {
                if (!$apoyo->desde || !$apoyo->hasta) {
                    return false;
                }

                // Comparación por día truncado: el apoyo cubre el día si
                // [inicio del día] cae dentro de [desde, hasta] a nivel fecha.
                return $apoyo->desde->copy()->startOfDay()->lte($dia)
                    && $apoyo->hasta->copy()->startOfDay()->gte($dia);
            })
            ->sortBy(fn ($apoyo) => ($apoyo->tipo->nombre ?? '') . '|' . $apoyo->id)
            ->values();
    }

    /**
     * Posición del día seleccionado dentro del rango del apoyo.
     * Devuelve null si el apoyo es de un solo día o el día no pertenece al rango.
     */
    public function posicionEnRango(Apoyo $apoyo): ?array
    {
        $dia = self::parsearFechaDia($this->diaSeleccionado);
        if ($dia === null || !$apoyo->desde || !$apoyo->hasta) {
            return null;
        }

        $desde = $apoyo->desde->copy()->startOfDay();
        $hasta = $apoyo->hasta->copy()->startOfDay();
        $dia = $dia->startOfDay();

        if ($dia->lt($desde) || $dia->gt($hasta)) {
            return null;
        }

        // diffInDays devuelve float en Carbon 3; redondear antes de castear.
        $total = (int) round($desde->diffInDays($hasta)) + 1;

        if ($total <= 1) {
            return null;
        }

        return [
            'actual' => (int) round($desde->diffInDays($dia)) + 1,
            'total' => $total,
        ];
    }

    /**
     * Parseo seguro de 'Y-m-d'. En Carbon 3 createFromFormat() lanza excepción
     * ante entrada inválida (no devuelve false como DateTime::createFromFormat).
     */
    private static function parsearFechaDia(?string $fecha): ?Carbon
    {
        if ($fecha === null || $fecha === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $fecha) ?: null;
        } catch (\Exception) {
            return null;
        }
    }

    public function nombreMesActual(): string
    {
        return Carbon::create($this->anio, $this->mes, 1)->locale('es')->translatedFormat('F Y');
    }

    #[Computed]
    public function apoyosDelMes()
    {
        $inicioMes = Carbon::create($this->anio, $this->mes, 1)->startOfDay();
        $finMes = Carbon::create($this->anio, $this->mes, 1)->endOfMonth()->endOfDay();

        return Apoyo::with(['tipo', 'organismo', 'unidades'])
            ->where('desde', '<=', $finMes)
            ->where('hasta', '>=', $inicioMes)
            ->orderBy('apoyos.id')
            ->get();
    }

    #[Computed]
    public function apoyosPorDia(): array
    {
        $inicioMes = Carbon::create($this->anio, $this->mes, 1)->startOfDay();
        $finMes = Carbon::create($this->anio, $this->mes, 1)->endOfMonth()->endOfDay();

        $apoyos = $this->apoyosDelMes;

        $porDia = [];

        foreach ($apoyos as $apoyo) {
            $desde = $apoyo->desde ? $apoyo->desde->copy()->startOfDay() : null;
            $hasta = $apoyo->hasta ? $apoyo->hasta->copy()->endOfDay() : null;

            if (!$desde || !$hasta) {
                continue;
            }

            $diaInicio = $desde->lte($inicioMes) ? $inicioMes->copy() : $desde->copy();
            $diaFin = $hasta->gte($finMes) ? $finMes->copy() : $hasta->copy();

            $current = $diaInicio->copy();
            $iteraciones = 0;

            while ($current->lte($diaFin)) {
                if (++$iteraciones > 366) {
                    Log::warning('Apoyos@apoyosPorDia: rango de fechas excesivo, loop cortado.', [
                        'apoyo_id' => $apoyo->id,
                        'desde' => $apoyo->desde?->format('Y-m-d H:i'),
                        'hasta' => $apoyo->hasta?->format('Y-m-d H:i'),
                        'iteraciones' => $iteraciones,
                    ]);
                    break;
                }

                $dia = (int) $current->day;
                if (!isset($porDia[$dia])) {
                    $porDia[$dia] = [];
                }

                $tipoId = $apoyo->tipo_id;
                if (!isset($porDia[$dia][$tipoId])) {
                    $porDia[$dia][$tipoId] = [
                        'color' => $apoyo->tipo->color ?? '#6c757d',
                        'nombre' => $apoyo->tipo->nombre ?? '—',
                        'count' => 0,
                    ];
                }
                $porDia[$dia][$tipoId]['count']++;

                // Reasignar (no mutar): con Date::use(CarbonImmutable::class) los casts
                // de Eloquent devuelven CarbonImmutable y addDay() no muta la instancia.
                $current = $current->addDay();
            }
        }

        return $porDia;
    }

    public function render()
    {
        return view('livewire.apoyos.index', [
            'apoyos' => $this->vistaActual === 'tabla' ? $this->apoyos : collect(),
            'diasCalendario' => $this->vistaActual === 'calendario' ? $this->diasCalendario() : [],
            'apoyosPorDia' => $this->vistaActual === 'calendario' ? $this->apoyosPorDia : [],
            'totalApoyos' => Apoyo::count(),
            'esHoy' => fn(int $dia) => $this->esHoy($dia),
        ]);
    }

    protected function diasCalendario(): array
    {
        $primerDiaMes = Carbon::create($this->anio, $this->mes, 1);
        $totalDias = $primerDiaMes->daysInMonth;

        $diasOffset = (int) $primerDiaMes->dayOfWeekIso - 1;

        $dias = array_fill(0, $diasOffset, null);

        for ($i = 1; $i <= $totalDias; $i++) {
            $dias[] = $i;
        }

        $totalCells = count($dias);
        $remainder = $totalCells % 7;
        if ($remainder > 0) {
            $dias = array_merge($dias, array_fill(0, 7 - $remainder, null));
        }

        return $dias;
    }

    protected function esHoy(int $dia): bool
    {
        $now = now();
        return $this->anio === (int) $now->year
            && $this->mes === (int) $now->month
            && $dia === (int) $now->day;
    }

    protected function reglasValidacion(): array
    {
        return [
            'formTipoId' => 'required|exists:tipos_apoyo,id',
            'formOrganismoId' => 'required|exists:organismos,id',
            'formDocumentoBusqueda' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (trim($value) !== '' && $this->formDocumentoNovedadId === null) {
                        $novedad = News::where('number', 'like', '%' . $value . '%')
                            ->orWhere('type', 'like', '%' . $value . '%')
                            ->first();
                        if (!$novedad) {
                            $fail('No se encontró una novedad con ese término.');
                        }
                    }
                },
            ],
            'formDesde' => 'required|date',
            'formHasta' => 'required|date|after_or_equal:formDesde',
            'formUnidades' => 'required|array|min:1',
            'formEstado' => 'required|in:' . implode(',', Apoyo::ESTADOS),
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formTipoId.required' => 'El tipo de apoyo es obligatorio.',
            'formTipoId.exists' => 'El tipo seleccionado no es válido.',
            'formOrganismoId.required' => 'El solicitante es obligatorio.',
            'formOrganismoId.exists' => 'El organismo seleccionado no es válido.',
            'formDesde.required' => 'La fecha de inicio es obligatoria.',
            'formHasta.required' => 'La fecha de fin es obligatoria.',
            'formHasta.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
            'formUnidades.required' => 'Debés seleccionar al menos una unidad.',
            'formUnidades.min' => 'Debés seleccionar al menos una unidad.',
            'formEstado.required' => 'El estado es obligatorio.',
            'formEstado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
