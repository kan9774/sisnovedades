<?php

namespace App\Livewire;

use App\Models\Conductor;
use App\Traits\UsesBootstrapPagination;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class Conductores extends Component
{
    use WithPagination;
    use UsesBootstrapPagination;

    // ── Estado de búsqueda ──
    public $search = '';

    // ── Estado del formulario ──
    public $showForm = false;
    public $formTipo = 'create';
    public $formConductorId = null;

    // ── Campos del formulario ──
    public $formGrado = '';
    public $formPrimerNombre = '';
    public $formSegundoNombre = '';
    public $formPrimerApellido = '';
    public $formSegundoApellido = '';
    public $formDocumento = '';
    public $formNroLicencia = '';
    public $formCategoriaLicencia = '';
    public $formFechaVencimientoLicencia = '';
    public $formLugarCarneSalud = '';
    public $formFechaVencimientoCarneSalud = '';
    public $formLugarCarneHabilitante = '';
    public $formNumeroCarneHabilitante = '';
    public $formFechaVencimientoCarneHabilitante = '';
    public $formTipoVehiculoHabilitado = '';
    public $formObservaciones = '';
    public $formActivo = false;

    // ── Feedback ──
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;
    public $confirmDeleteId = null;

    // ── Estado del modal de detalle (show) ──
    public $showDetalle = false;
    public $detalleConductorId = null;
    public $detalleConductor = null;

    // ── mount: autorización de acceso ──
    public function mount()
    {
        $this->authorize('viewAny', Conductor::class);
    }

    // ── Consulta con caché ──
    #[Computed]
    public function conductores()
    {
        $query = Conductor::orderBy('primer_apellido');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('primer_apellido', 'like', '%' . $this->search . '%')
                  ->orWhere('primer_nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('documento', 'like', '%' . $this->search . '%');
            });
        }

        return $query->paginate(15);
    }

    // ── ABRIR FORMULARIO DE ALTA ──
    public function crear()
    {
        try {
            $this->authorize('create', Conductor::class);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetForm();
        $this->formTipo = 'create';
        $this->showForm = true;
        $this->resetErrorBag();
        $this->dispatch('abrir-modal-conductor');
    }

    // ── ABRIR FORMULARIO DE EDICIÓN ──
    public function abrirEditar(int $conductorId)
    {
        $conductor = Conductor::findOrFail($conductorId);

        try {
            $this->authorize('update', $conductor);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->resetErrorBag();
        $this->formTipo = 'edit';
        $this->formConductorId = $conductor->id;
        $this->formGrado = $conductor->grado;
        $this->formPrimerNombre = $conductor->primer_nombre;
        $this->formSegundoNombre = $conductor->segundo_nombre;
        $this->formPrimerApellido = $conductor->primer_apellido;
        $this->formSegundoApellido = $conductor->segundo_apellido;
        $this->formDocumento = $conductor->documento;
        $this->formNroLicencia = $conductor->nro_licencia;
        $this->formCategoriaLicencia = $conductor->categoria_licencia;
        $this->formFechaVencimientoLicencia = $conductor->fecha_vencimiento_licencia?->format('Y-m-d');
        $this->formLugarCarneSalud = $conductor->lugar_carne_salud;
        $this->formFechaVencimientoCarneSalud = $conductor->fecha_vencimiento_carne_salud?->format('Y-m-d');
        $this->formLugarCarneHabilitante = $conductor->lugar_carne_habilitante;
        $this->formNumeroCarneHabilitante = $conductor->numero_carne_habilitante;
        $this->formFechaVencimientoCarneHabilitante = $conductor->fecha_vencimiento_carne_habilitante?->format('Y-m-d');
        $this->formTipoVehiculoHabilitado = $conductor->tipo_vehiculo_habilitado;
        $this->formObservaciones = $conductor->observaciones;
        $this->formActivo = (bool) $conductor->activo;
        $this->showForm = true;
        $this->dispatch('abrir-modal-conductor');
    }

    // ── CERRAR FORMULARIO ──
    public function cerrarForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
        $this->dispatch('cerrar-modal-conductor');
    }

    // ── GUARDAR (create o update) ──
    public function guardar()
    {
        try {
            if ($this->formTipo === 'create') {
                $this->authorize('create', Conductor::class);
            } else {
                $conductor = Conductor::findOrFail($this->formConductorId);
                $this->authorize('update', $conductor);
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
                Conductor::create($data);
                $this->successMsg = 'Conductor creado correctamente.';
            } else {
                $conductor = Conductor::findOrFail($this->formConductorId);
                $conductor->update($data);
                $this->successMsg = 'Conductor actualizado correctamente.';
            }

            $this->showForm = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // ── DATOS VALIDADOS (array limpio) ──
    protected function datosValidados(): array
    {
        $data = [
            'grado' => $this->formGrado,
            'primer_nombre' => $this->formPrimerNombre,
            'segundo_nombre' => $this->formSegundoNombre ?: null,
            'primer_apellido' => $this->formPrimerApellido,
            'segundo_apellido' => $this->formSegundoApellido ?: null,
            'documento' => $this->formDocumento,
            'nro_licencia' => $this->formNroLicencia,
            'categoria_licencia' => $this->formCategoriaLicencia,
            'fecha_vencimiento_licencia' => $this->formFechaVencimientoLicencia ?: null,
            'lugar_carne_salud' => $this->formLugarCarneSalud ?: null,
            'fecha_vencimiento_carne_salud' => $this->formFechaVencimientoCarneSalud ?: null,
            'lugar_carne_habilitante' => $this->formLugarCarneHabilitante ?: null,
            'numero_carne_habilitante' => $this->formNumeroCarneHabilitante ?: null,
            'fecha_vencimiento_carne_habilitante' => $this->formFechaVencimientoCarneHabilitante ?: null,
            'tipo_vehiculo_habilitado' => $this->formTipoVehiculoHabilitado ?: null,
            'observaciones' => $this->formObservaciones ?: null,
            'activo' => $this->formActivo,
        ];

        // Protección real: si no es admin en modo edit, remover documento
        if ($this->formTipo === 'edit' && !auth()->user()->isAdmin()) {
            unset($data['documento']);
        }

        return $data;
    }

    // ── ELIMINAR (confirmación + ejecución separada) ──
    public function confirmarEliminacion(int $conductorId)
    {
        $conductor = Conductor::findOrFail($conductorId);

        try {
            $this->authorize('delete', $conductor);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        // Solo bloquea si tiene salidas activas (sin cerrar)
        if ($conductor->salidasVehiculos()->whereNull('hora_entra')->exists()) {
            $this->errorMsg = 'No se puede eliminar un conductor con salidas de vehículo activas (sin cerrar).';
            return;
        }

        $this->confirmDeleteId = $conductorId;
    }

    public function ejecutarEliminacion()
    {
        $this->loading = true;
        try {
            $conductor = Conductor::findOrFail($this->confirmDeleteId);
            $conductor->delete();
            $this->successMsg = 'Conductor eliminado correctamente.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->confirmDeleteId = null;
        }
    }

    // ── VER DETALLE (show) ──
    public function verDetalle(int $conductorId)
    {
        $conductor = Conductor::findOrFail($conductorId);

        try {
            $this->authorize('view', $conductor);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->detalleConductorId = $conductorId;
        $this->detalleConductor = Conductor::with(['salidasVehiculos' => function($query) {
            $query->latest('hora_sale')->limit(10);
        }])->findOrFail($conductorId);
        $this->showDetalle = true;
    }

    // ── CERRAR DETALLE ──
    public function cerrarDetalle()
    {
        $this->showDetalle = false;
        $this->detalleConductorId = null;
        $this->detalleConductor = null;
    }

    // ── ABRIR EDICIÓN DESDE DETALLE ──
    public function abrirEditarDesdeDetalle()
    {
        $this->cerrarDetalle();
        $this->abrirEditar($this->detalleConductorId);
    }

    // ── RESET DE CAMPOS ──
    protected function resetForm(): void
    {
        $this->formTipo = 'create';
        $this->formConductorId = null;
        $this->formGrado = '';
        $this->formPrimerNombre = '';
        $this->formSegundoNombre = '';
        $this->formPrimerApellido = '';
        $this->formSegundoApellido = '';
        $this->formDocumento = '';
        $this->formNroLicencia = '';
        $this->formCategoriaLicencia = '';
        $this->formFechaVencimientoLicencia = '';
        $this->formLugarCarneSalud = '';
        $this->formFechaVencimientoCarneSalud = '';
        $this->formLugarCarneHabilitante = '';
        $this->formNumeroCarneHabilitante = '';
        $this->formFechaVencimientoCarneHabilitante = '';
        $this->formTipoVehiculoHabilitado = '';
        $this->formObservaciones = '';
        $this->formActivo = false;
    }

    // ── REGLAS DE VALIDACIÓN ──
    protected function reglasValidacion(): array
    {
        $unique = $this->formTipo === 'create'
            ? 'unique:conductores,documento'
            : 'unique:conductores,documento,' . $this->formConductorId;

        $rules = [
            'formGrado' => 'required|string|max:50',
            'formPrimerNombre' => 'required|string|max:100',
            'formSegundoNombre' => 'nullable|string|max:100',
            'formPrimerApellido' => 'required|string|max:100',
            'formSegundoApellido' => 'nullable|string|max:100',
            'formDocumento' => 'required|string|max:20|' . $unique,
            'formNroLicencia' => 'required|string|max:50',
            'formCategoriaLicencia' => 'required|string|max:10',
            'formLugarCarneSalud' => 'nullable|string|max:255',
            'formLugarCarneHabilitante' => 'nullable|string|max:255',
            'formNumeroCarneHabilitante' => 'nullable|string|max:50',
            'formTipoVehiculoHabilitado' => 'nullable|string|max:100',
            'formObservaciones' => 'nullable|string',
            'formActivo' => 'boolean',
        ];

        if ($this->formTipo === 'create') {
            // CREATE: fechas estrictas, deben ser futuras
            $rules['formFechaVencimientoLicencia'] = 'required|date|after:today';
            $rules['formFechaVencimientoCarneSalud'] = 'nullable|date|after:today';
            $rules['formFechaVencimientoCarneHabilitante'] = 'nullable|date|after:today';
        } else {
            // EDIT: fechas flexibles, sin restricción de futuro
            $rules['formFechaVencimientoLicencia'] = 'required|date';
            $rules['formFechaVencimientoCarneSalud'] = 'nullable|date';
            $rules['formFechaVencimientoCarneHabilitante'] = 'nullable|date';
        }

        return $rules;
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formGrado.required' => 'El grado es obligatorio.',
            'formPrimerNombre.required' => 'El primer nombre es obligatorio.',
            'formPrimerApellido.required' => 'El primer apellido es obligatorio.',
            'formDocumento.required' => 'El documento es obligatorio.',
            'formDocumento.unique' => 'Ya existe un conductor con ese documento.',
            'formNroLicencia.required' => 'El número de licencia es obligatorio.',
            'formCategoriaLicencia.required' => 'La categoría de licencia es obligatoria.',
            'formFechaVencimientoLicencia.required' => 'La fecha de vencimiento de licencia es obligatoria.',
            'formFechaVencimientoLicencia.after' => 'La fecha de vencimiento debe ser futura.',
        ];
    }

    // ── REACTIVO: al cambiar búsqueda, resetear página ──
    public function updatedSearch()
    {
        $this->resetPage();
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
        return view('livewire.conductores.index', [
            'conductores' => $this->conductores(),
        ]);
    }
}
