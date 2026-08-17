<div>
    <x-ops-card title="Conductores" icon="user-tie" eyebrow="{{ $conductores->total() }} registros">
        <x-slot name="actions">
            @can('create', \App\Models\Conductor::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                    Nuevo Conductor
                </x-btn-ops>
            @endcan
        </x-slot>

        {{-- BARRA DE BÚSQUEDA --}}
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="form-control border-left-0"
                        placeholder="Buscar por nombre o documento...">
                </div>
            </div>
            <div class="col-md-4 text-right">
                @if ($search)
                    <button wire:click="limpiarFiltros" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-times"></i> Limpiar filtros
                    </button>
                @endif
            </div>
        </div>

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-ops">
                    <tr>
                        <th>Grado</th>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Licencia</th>
                        <th>Categoría</th>
                        <th>Venc. Licencia</th>
                        <th>Estado</th>
                        <th class="text-center" style="width: 120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conductores as $conductor)
                        <tr wire:key="conductor-{{ $conductor->id }}">
                            <td>{{ $conductor->grado }}</td>
                            <td>{{ $conductor->nombre_completo }}</td>
                            <td>{{ $conductor->documento }}</td>
                            <td>{{ $conductor->nro_licencia }}</td>
                            <td>{{ $conductor->categoria_licencia }}</td>
                            <td>
                                @if ($conductor->licencia_vigente)
                                    <span class="badge-ops badge-ops-success">{{ $conductor->fecha_vencimiento_licencia->format('d/m/Y') }}</span>
                                @else
                                    <span class="badge-ops badge-ops-danger">{{ $conductor->fecha_vencimiento_licencia->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($conductor->activo)
                                    <span class="badge-ops badge-ops-success">Activo</span>
                                @else
                                    <span class="badge-ops badge-ops-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <div class="ops-actions justify-content-center">
                                    @can('view', $conductor)
                                        <x-btn-ops variant="info" icon="eye"
                                            wire:click="verDetalle({{ $conductor->id }})"
                                            size="xs" title="Ver detalle">
                                        </x-btn-ops>
                                    @endcan
                                    @can('update', $conductor)
                                        <x-btn-ops variant="warning" icon="pen"
                                            wire:click="abrirEditar({{ $conductor->id }})"
                                            size="xs" title="Editar">
                                        </x-btn-ops>
                                    @endcan
                                    @can('delete', $conductor)
                                        <x-btn-ops variant="danger" icon="trash"
                                            wire:click="confirmarEliminacion({{ $conductor->id }})"
                                            size="xs" title="Eliminar">
                                        </x-btn-ops>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-user-tie fa-2x d-block mb-2"></i>
                                No hay conductores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($conductores->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $conductores->links() }}
            </div>
        @endif
    </x-ops-card>

    {{-- MODAL: FORMULARIO CREAR / EDITAR (ops-panel overlay) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalConductor"
         x-data
         x-init="$watch('$wire.showForm', value => {
             if (value) document.body.classList.add('ops-panel-open');
             else document.body.classList.remove('ops-panel-open');
         })"
         :class="{ 'is-open': $wire.showForm }"
         wire:click.self="cerrarForm">
        <div class="ops-panel">
            <div class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Administración</span>
                        <h5 class="ops-panel__title">
                            @if ($formTipo === 'create')
                                Nuevo Conductor
                            @else
                                Editar Conductor
                            @endif
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" wire:click="cerrarForm"
                        title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body" wire:loading.class="opacity-50" wire:target="guardar">
                    <div class="ops-panel__content">
                        @if ($errorMsg && !$showForm)
                            <div class="alert alert-danger">{{ $errorMsg }}</div>
                        @endif
                        <form wire:submit="guardar" id="form-conductor">
                            {{-- Fila 1: Grado, Documento, Primer Nombre, Segundo Nombre --}}
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Grado <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="formGrado"
                                            class="form-control @error('formGrado') is-invalid @enderror"
                                            placeholder="Ej: Sgto.(EC)">
                                        @error('formGrado')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Documento <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="formDocumento"
                                            class="form-control @error('formDocumento') is-invalid @enderror"
                                            placeholder="Cédula de identidad">
                                        @error('formDocumento')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Primer Nombre <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="formPrimerNombre"
                                            class="form-control @error('formPrimerNombre') is-invalid @enderror"
                                            placeholder="Primer nombre">
                                        @error('formPrimerNombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Segundo Nombre</label>
                                        <input type="text" wire:model="formSegundoNombre"
                                            class="form-control @error('formSegundoNombre') is-invalid @enderror"
                                            placeholder="Segundo nombre">
                                        @error('formSegundoNombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Fila 2: Primer Apellido, Segundo Apellido, Activo --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Primer Apellido <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="formPrimerApellido"
                                            class="form-control @error('formPrimerApellido') is-invalid @enderror"
                                            placeholder="Primer apellido">
                                        @error('formPrimerApellido')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Segundo Apellido</label>
                                        <input type="text" wire:model="formSegundoApellido"
                                            class="form-control @error('formSegundoApellido') is-invalid @enderror"
                                            placeholder="Segundo apellido">
                                        @error('formSegundoApellido')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div class="custom-control custom-switch pt-2">
                                            <input type="checkbox"
                                                class="custom-control-input"
                                                id="formActivo"
                                                wire:model="formActivo">
                                            <label class="custom-control-label" for="formActivo">Activo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- Licencia de conducir --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>N° Licencia <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="formNroLicencia"
                                            class="form-control @error('formNroLicencia') is-invalid @enderror"
                                            placeholder="Número de licencia">
                                        @error('formNroLicencia')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Categoría <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="formCategoriaLicencia"
                                            class="form-control @error('formCategoriaLicencia') is-invalid @enderror"
                                            placeholder="Ej: B, C, D">
                                        @error('formCategoriaLicencia')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Vencimiento Licencia <span class="text-danger">*</span></label>
                                        <input type="date" wire:model="formFechaVencimientoLicencia"
                                            class="form-control @error('formFechaVencimientoLicencia') is-invalid @enderror">
                                        @error('formFechaVencimientoLicencia')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Carné de Salud --}}
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Lugar Carné Salud</label>
                                        <input type="text" wire:model="formLugarCarneSalud"
                                            class="form-control @error('formLugarCarneSalud') is-invalid @enderror"
                                            placeholder="Ej: Hospital Militar">
                                        @error('formLugarCarneSalud')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Vencimiento Carné Salud</label>
                                        <input type="date" wire:model="formFechaVencimientoCarneSalud"
                                            class="form-control @error('formFechaVencimientoCarneSalud') is-invalid @enderror">
                                        @error('formFechaVencimientoCarneSalud')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Carné Habilitante --}}
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>N° Carné Habilitante</label>
                                        <input type="text" wire:model="formNumeroCarneHabilitante"
                                            class="form-control @error('formNumeroCarneHabilitante') is-invalid @enderror"
                                            placeholder="Ej: CH-9874">
                                        @error('formNumeroCarneHabilitante')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Lugar Carné Habilitante</label>
                                        <input type="text" wire:model="formLugarCarneHabilitante"
                                            class="form-control @error('formLugarCarneHabilitante') is-invalid @enderror"
                                            placeholder="Ej: Escuela de Conductores">
                                        @error('formLugarCarneHabilitante')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Vencimiento Carné Habilitante</label>
                                        <input type="date" wire:model="formFechaVencimientoCarneHabilitante"
                                            class="form-control @error('formFechaVencimientoCarneHabilitante') is-invalid @enderror">
                                        @error('formFechaVencimientoCarneHabilitante')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Tipo vehículo habilitado --}}
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Tipo Vehículo Habilitado</label>
                                        <input type="text" wire:model="formTipoVehiculoHabilitado"
                                            class="form-control @error('formTipoVehiculoHabilitado') is-invalid @enderror"
                                            placeholder="Ej: 7Ton, 18 Pasajeros, S/Limite de Peso">
                                        @error('formTipoVehiculoHabilitado')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Observaciones --}}
                            <div class="form-group mt-3">
                                <label>Observaciones</label>
                                <textarea wire:model="formObservaciones" rows="3"
                                    class="form-control @error('formObservaciones') is-invalid @enderror"
                                    placeholder="Observaciones adicionales..."></textarea>
                                @error('formObservaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </form>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary"
                        wire:click="cerrarForm"
                        wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                        Cancelar
                    </button>
                    <button type="submit" form="form-conductor" class="btn btn-ops-primary"
                        wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                        @if ($loading)
                            <span class="spinner-border spinner-border-sm mr-1"></span>
                        @endif
                        <i class="fas fa-save"></i>
                        {{ $formTipo === 'create' ? 'Crear Conductor' : 'Guardar Cambios' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- MODAL: CONFIRMAR ELIMINACIÓN --}}
    @if ($confirmDeleteId)
        <div class="modal fade show d-block" tabindex="-1"
            style="background: rgba(255, 255, 255, 0.15) !important; backdrop-filter: blur(12px) saturate(180%) !important; -webkit-backdrop-filter: blur(12px) saturate(180%) !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; border-radius: 16px !important; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;"
            wire:click.self="$set('confirmDeleteId', null)" wire:keydown.escape="$set('confirmDeleteId', null)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar eliminación</h5>
                        <button type="button" class="close text-white"
                            wire:click="$set('confirmDeleteId', null)">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar este conductor?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('confirmDeleteId', null)">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="ejecutarEliminacion"
                            @disabled($loading)>
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span> Eliminando...
                            @else
                                <i class="fas fa-trash"></i> Eliminar
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: DETALLE / SHOW (ops-panel overlay) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalDetalleConductor"
         x-data
         x-init="$watch('$wire.showDetalle', value => {
             if (value) document.body.classList.add('ops-panel-open');
             else document.body.classList.remove('ops-panel-open');
         })"
         :class="{ 'is-open': $wire.showDetalle }"
         wire:click.self="cerrarDetalle">
        <div class="ops-panel ops-panel--conductor">
            <div class="ops-panel__form">
                <div class="ops-panel__header">
                    <span class="conductor-avatar">
                        {{ strtoupper(substr($detalleConductor?->nombre_completo ?? 'X', 0, 1)) }}{{ strtoupper(substr($detalleConductor?->nombre_completo ?? '', strpos($detalleConductor?->nombre_completo ?? ' ', ' ') + 1, 1)) }}
                    </span>
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Detalle</span>
                        <h5 class="ops-panel__title">
                            Conductor: <strong>{{ $detalleConductor?->nombre_completo ?? '' }}</strong>
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" wire:click="cerrarDetalle"
                        title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        @if ($detalleConductor)
                            {{-- Pestañas --}}
                            <ul class="nav nav-tabs nav-tabs-ops mb-3" id="detalleTabs">
                                <li class="nav-item">
                                    <a class="nav-link nav-link-ops active" data-toggle="tab" href="#detalleInfo">
                                        <i class="fas fa-user"></i> Información
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-ops" data-toggle="tab" href="#detalleDoc">
                                        <i class="fas fa-folder-open"></i> Documentación
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-ops" data-toggle="tab" href="#detalleHistorial">
                                        <i class="fas fa-route"></i> Últimas Salidas
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                {{-- TAB: Información --}}
                                <div class="tab-pane active" id="detalleInfo">

                                    {{-- Sección: Datos Personales --}}
                                    <div class="conductor-section">
                                        <div class="conductor-section__title">
                                            <i class="fas fa-id-badge"></i> Datos Personales
                                        </div>
                                        <div class="conductor-field">
                                            <span class="conductor-field__label">
                                                <i class="fas fa-id-card"></i> Documento
                                            </span>
                                            <span class="conductor-field__value">{{ $detalleConductor->documento }}</span>
                                        </div>
                                        <div class="conductor-field">
                                            <span class="conductor-field__label">
                                                <i class="fas fa-user"></i> Grado
                                            </span>
                                            <span class="conductor-field__value">{{ $detalleConductor->grado }}</span>
                                        </div>
                                        <div class="conductor-field">
                                            <span class="conductor-field__label">
                                                <i class="fas fa-user"></i> Nombre completo
                                            </span>
                                            <span class="conductor-field__value">{{ $detalleConductor->nombre_completo }}</span>
                                        </div>
                                        <div class="conductor-field">
                                            <span class="conductor-field__label">
                                                <i class="fas fa-clipboard-list"></i> Estado
                                            </span>
                                            <span class="conductor-field__value">
                                                @if ($detalleConductor->activo)
                                                    <span class="conductor-badge-vigencia conductor-badge-vigencia--vigente">
                                                        <i class="fas fa-check-circle"></i> Activo
                                                    </span>
                                                @else
                                                    <span class="conductor-badge-vigencia conductor-badge-vigencia--vencido">
                                                        <i class="fas fa-times-circle"></i> Inactivo
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Sección: Licencia --}}
                                    <div class="conductor-section">
                                        <div class="conductor-section__title">
                                            <i class="fas fa-driver"></i> Licencia
                                        </div>
                                        <div class="conductor-field">
                                            <span class="conductor-field__label">
                                                <i class="fas fa-id-card"></i> N° Licencia
                                            </span>
                                            <span class="conductor-field__value fw-bold">{{ $detalleConductor->nro_licencia }}</span>
                                        </div>
                                        <div class="conductor-field">
                                            <span class="conductor-field__label">
                                                <i class="fas fa-layer-group"></i> Categoría
                                            </span>
                                            <span class="conductor-field__value">{{ $detalleConductor->categoria_licencia }}</span>
                                        </div>
                                        <div class="conductor-field">
                                            <span class="conductor-field__label">
                                                <i class="fas fa-calendar-check"></i> Vencimiento
                                            </span>
                                            <span class="conductor-field__value">
                                                @if ($detalleConductor->licencia_vigente)
                                                    <span class="conductor-badge-vigencia conductor-badge-vigencia--vigente">
                                                        <i class="fas fa-check-circle"></i>
                                                        {{ $detalleConductor->fecha_vencimiento_licencia->format('d/m/Y') }}
                                                    </span>
                                                @else
                                                    <span class="conductor-badge-vigencia conductor-badge-vigencia--vencido">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        {{ $detalleConductor->fecha_vencimiento_licencia->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="conductor-field">
                                            <span class="conductor-field__label">
                                                <i class="fas fa-car"></i> Tipo Veh. Habilitado
                                            </span>
                                            <span class="conductor-field__value">{{ $detalleConductor->tipo_vehiculo_habilitado ?? '—' }}</span>
                                        </div>
                                    </div>

                                    {{-- Sección: Observaciones --}}
                                    <div class="conductor-section">
                                        <div class="conductor-section__title">
                                            <i class="fas fa-sticky-note"></i> Observaciones
                                        </div>
                                        <div class="conductor-observaciones">
                                            {{ $detalleConductor->observaciones ?? 'Sin observaciones registradas.' }}
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB: Documentación --}}
                                <div class="tab-pane" id="detalleDoc">
                                    <div class="row g-3">

                                        {{-- Licencia --}}
                                        <div class="col-md-4">
                                            <div class="doc-card">
                                                <div class="doc-card__header">
                                                    <h6><i class="fas fa-id-card"></i> Licencia de Conducir</h6>
                                                </div>
                                                <div class="doc-card__body">
                                                    <span class="doc-card__status {{ $detalleConductor->licencia_vigente ? 'doc-card__status--vigente' : 'doc-card__status--vencido' }}">
                                                        <i class="fas {{ $detalleConductor->licencia_vigente ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                                                        {{ $detalleConductor->licencia_vigente ? 'Vigente' : 'Vencido' }}
                                                    </span>
                                                    <div class="doc-card__row">Nº: <strong>{{ $detalleConductor->nro_licencia }}</strong></div>
                                                    <div class="doc-card__row">Categoría: <strong>{{ $detalleConductor->categoria_licencia }}</strong></div>
                                                    <div class="doc-card__row mt-2">
                                                        <i class="fas fa-calendar-alt text-muted"></i>
                                                        Vence: <strong>{{ $detalleConductor->fecha_vencimiento_licencia->format('d/m/Y') }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Carné de Salud --}}
                                        <div class="col-md-4">
                                            <div class="doc-card">
                                                <div class="doc-card__header">
                                                    <h6><i class="fas fa-heartbeat"></i> Carné de Salud</h6>
                                                </div>
                                                <div class="doc-card__body">
                                                    @if ($detalleConductor->fecha_vencimiento_carne_salud)
                                                        <span class="doc-card__status {{ $detalleConductor->carne_salud_vigente ? 'doc-card__status--vigente' : 'doc-card__status--vencido' }}">
                                                            <i class="fas {{ $detalleConductor->carne_salud_vigente ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                                                            {{ $detalleConductor->carne_salud_vigente ? 'Vigente' : 'Vencido' }}
                                                        </span>
                                                        <div class="doc-card__row">Lugar: <strong>{{ $detalleConductor->lugar_carne_salud ?? 'No especificado' }}</strong></div>
                                                        <div class="doc-card__row mt-2">
                                                            <i class="fas fa-calendar-alt text-muted"></i>
                                                            Vence: <strong>{{ $detalleConductor->fecha_vencimiento_carne_salud->format('d/m/Y') }}</strong>
                                                        </div>
                                                    @else
                                                        <div class="doc-card__empty">
                                                            <i class="fas fa-file-medical fa-2x d-block mb-2"></i>
                                                            Sin Carné de Salud
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Carné Habilitante --}}
                                        <div class="col-md-4">
                                            <div class="doc-card">
                                                <div class="doc-card__header">
                                                    <h6><i class="fas fa-certificate"></i> Carné Habilitante</h6>
                                                </div>
                                                <div class="doc-card__body">
                                                    @if ($detalleConductor->fecha_vencimiento_carne_habilitante)
                                                        <span class="doc-card__status {{ $detalleConductor->carne_habilitante_vigente ? 'doc-card__status--vigente' : 'doc-card__status--vencido' }}">
                                                            <i class="fas {{ $detalleConductor->carne_habilitante_vigente ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                                                            {{ $detalleConductor->carne_habilitante_vigente ? 'Vigente' : 'Vencido' }}
                                                        </span>
                                                        <div class="doc-card__row">Nº: <strong>{{ $detalleConductor->numero_carne_habilitante ?? 'N/A' }}</strong></div>
                                                        <div class="doc-card__row">Habilitado: <strong>{{ $detalleConductor->tipo_vehiculo_habilitado ?? 'General' }}</strong></div>
                                                        <div class="doc-card__row mt-2">
                                                            <i class="fas fa-calendar-alt text-muted"></i>
                                                            Vence: <strong>{{ $detalleConductor->fecha_vencimiento_carne_habilitante->format('d/m/Y') }}</strong>
                                                        </div>
                                                    @else
                                                        <div class="doc-card__empty">
                                                            <i class="fas fa-certificate fa-2x d-block mb-2"></i>
                                                            Sin Carné Habilitante
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- TAB: Historial de salidas --}}
                                <div class="tab-pane" id="detalleHistorial">
                                    @php
                                        $salidas = $detalleConductor->salidasVehiculos()->with(['vehiculo', 'guardia'])->latest('hora_sale')->limit(10)->get();
                                    @endphp
                                    @if ($salidas->count() > 0)
                                        @foreach ($salidas as $salida)
                                            <div class="salida-card">
                                                <div class="salida-card__icon {{ $salida->estado === 'cerrada' ? 'salida-card__icon--cerrada' : 'salida-card__icon--pendiente' }}">
                                                    <i class="fas {{ $salida->estado === 'cerrada' ? 'fa-check' : 'fa-clock' }}"></i>
                                                </div>
                                                <div class="salida-card__info">
                                                    <h6>{{ $salida->vehiculo?->matricula ?? 'N/A' }}</h6>
                                                    <small>
                                                        <i class="fas fa-calendar"></i>
                                                        {{ $salida->guardia?->date?->format('d/m/Y') ?? '—' }}
                                                        &nbsp;·&nbsp;
                                                        <i class="fas fa-clock"></i>
                                                        Sale: {{ $salida->hora_sale ? $salida->hora_sale->format('H:i') : '—' }}
                                                        @if ($salida->hora_entra)
                                                            &nbsp;·&nbsp; Entra: {{ $salida->hora_entra->format('H:i') }}
                                                        @endif
                                                    </small>
                                                </div>
                                                <div class="salida-card__meta">
                                                    <small>Kms</small>
                                                    <strong>{{ $salida->kms_recorridos ?? '—' }}</strong>
                                                </div>
                                                <span class="salida-card__badge {{ $salida->estado === 'cerrada' ? 'badge-ops-success' : 'badge-ops-danger' }}">
                                                    {{ $salida->estado === 'cerrada' ? 'Cerrada' : 'Pendiente' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-route fa-2x text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-0">No se registran salidas recientes para este conductor.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ops-panel__footer">
                    @if ($detalleConductor && $detalleConductor->salidasVehiculos()->count() > 0)
                        <div class="salidas-preview">
                            <i class="fas fa-route"></i>
                            <strong>{{ $detalleConductor->salidasVehiculos()->count() }}</strong> salida{{ $detalleConductor->salidasVehiculos()->count() > 1 ? 's' : '' }} registrada{{ $detalleConductor->salidasVehiculos()->count() > 1 ? 's' : '' }}
                        </div>
                    @endif
                    <div style="display: flex; justify-content: flex-end; gap: .5rem;">
                        <button type="button" class="btn btn-outline-secondary"
                            wire:click="cerrarDetalle">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                        @if ($detalleConductor && auth()->user()->can('update', $detalleConductor))
                            <button type="button" class="btn btn-ops-primary"
                                wire:click="abrirEditarDesdeDetalle">
                                <i class="fas fa-pen"></i> Editar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    </template>

    @script
    <script>
        $wire.$watch('successMsg', (valor) => {
            mostrarToast('success', valor);
        });

        $wire.$watch('errorMsg', (valor) => {
            mostrarToast('error', valor);
        });
    </script>
    @endscript
</div>
