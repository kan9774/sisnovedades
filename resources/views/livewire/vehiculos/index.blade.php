<div>
    <x-ops-card title="Vehículos" icon="truck" eyebrow="{{ $vehiculos->total() ?? 0 }} registros">
        <x-slot name="actions">
            @if (!$vistaPapelera)
                @can('create', \App\Models\Vehiculo::class)
                    <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                        Nuevo Vehículo
                    </x-btn-ops>
                @endcan
            @endif
        </x-slot>

        {{-- PESTAÑAS: Activos / Papelera --}}
        <x-nav-tabs-ops :tabs="['activos' => 'Vehículos activos', 'papelera' => 'Papelera']" :active="$vistaPapelera ? 'papelera' : 'activos'"
            wireMethod="{{ $vistaPapelera ? 'verActivos' : 'verPapelera' }}" :livewire="true" />

        {{-- BARRA DE BÚSQUEDA --}}
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-left-0"
                        placeholder="Buscar por matrícula, marca, modelo o vehículo...">
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
                        <th>Matrícula</th>
                        <th>Marca / Modelo</th>
                        <th>Unidad</th>
                        <th>Tipo Vehículo</th>
                        <th>Estado</th>
                        @if ($vistaPapelera)
                            <th>Fecha eliminación</th>
                        @else
                            <th>Activo</th>
                        @endif
                        <th class="text-center" style="width: 120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehiculos as $vehiculo)
                        <tr wire:key="vehiculo-{{ $vehiculo->id }}">
                            <td class="fw-bold">{{ $vehiculo->matricula }}</td>
                            <td>{{ $vehiculo->marca ?? '—' }} {{ $vehiculo->modelo ?? '' }}</td>
                            <td>{{ $vehiculo->unidad->nombre ?? '—' }}</td>
                            <td>{{ $vehiculo->tipoVehiculo->nombre ?? '—' }}</td>
                            <td>
                                <span class="{{ $vehiculo->estado_badge_class }}">{{ $vehiculo->estado_label }}</span>
                            </td>
                            @if (!$vistaPapelera)
                                <td class="text-center align-middle">
                                    @if ($vehiculo->activo)
                                        <span class="badge-ops badge-ops-success">Sí</span>
                                    @else
                                        <span class="badge-ops badge-ops-secondary">No</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <div class="ops-actions justify-content-center">
                                        @can('view', $vehiculo)
                                            <x-btn-ops variant="info" icon="eye"
                                                wire:click="verDetalle({{ $vehiculo->id }})" size="xs"
                                                title="Ver detalle"></x-btn-ops>
                                        @endcan
                                        @can('update', $vehiculo)
                                            <x-btn-ops variant="warning" icon="pen"
                                                wire:click="abrirEditar({{ $vehiculo->id }})" size="xs"
                                                title="Editar"></x-btn-ops>
                                        @endcan
                                        @can('delete', $vehiculo)
                                            <x-btn-ops variant="danger" icon="trash"
                                                wire:click="confirmarEliminacion({{ $vehiculo->id }})" size="xs"
                                                title="Eliminar"></x-btn-ops>
                                        @endcan
                                    </div>
                                </td>
                            @else
                                <td class="text-center align-middle">
                                    <small
                                        class="text-muted">{{ $vehiculo->deleted_at ? $vehiculo->deleted_at->format('d/m/Y H:i') : '—' }}</small>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="ops-actions justify-content-center">
                                        @can('restore', $vehiculo)
                                            <x-btn-ops variant="success" icon="undo"
                                                wire:click="restaurar({{ $vehiculo->id }})" size="xs"
                                                title="Restaurar"></x-btn-ops>
                                        @endcan
                                        @can('forceDelete', $vehiculo)
                                            <x-btn-ops variant="danger" icon="trash"
                                                wire:click="confirmarEliminacionPermanente({{ $vehiculo->id }})"
                                                size="xs" title="Eliminar permanentemente"></x-btn-ops>
                                        @endcan
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-truck fa-2x d-block mb-2"></i>
                                @if ($vistaPapelera)
                                    No hay vehículos en la papelera.
                                @else
                                    No hay vehículos registrados.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($vehiculos->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $vehiculos->links() }}
            </div>
        @endif
    </x-ops-card>

    {{-- MODAL: FORMULARIO CREAR / EDITAR (ops-panel overlay) --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalVehiculo" x-data x-init="$watch('$wire.showForm', value => {
            if (value) document.body.classList.add('ops-panel-open');
            else document.body.classList.remove('ops-panel-open');
        })"
            :class="{ 'is-open': $wire.showForm }" wire:click.self="cerrarForm">
            <div class="ops-panel">
                <div class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">BCOM1 · Administración</span>
                            <h5 class="ops-panel__title">
                                @if ($formTipo === 'create')
                                    Nuevo Vehículo
                                @else
                                    Editar Vehículo
                                @endif
                            </h5>
                        </div>
                        <button type="button" class="ops-panel__close" wire:click="cerrarForm" title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body" wire:loading.class="opacity-50" wire:target="guardar">
                        <div class="ops-panel__content">
                            <form wire:submit="guardar" id="form-vehiculo">

                                {{-- ═══ SECCIÓN 1: DATOS GENERALES ═══ --}}
                                <x-ops-card title="Datos Generales" icon="info-circle">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Matrícula <span class="text-danger">*</span></label>
                                                <input type="text" wire:model="formMatricula"
                                                    class="form-control form-control-sm @error('formMatricula') is-invalid @enderror"
                                                    placeholder="Ej: AA-BB-123">
                                                @error('formMatricula')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Marca</label>
                                                <input type="text" wire:model="formMarca"
                                                    class="form-control form-control-sm @error('formMarca') is-invalid @enderror"
                                                    placeholder="Ej: Ford">
                                                @error('formMarca')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Modelo</label>
                                                <input type="text" wire:model="formModelo"
                                                    class="form-control form-control-sm @error('formModelo') is-invalid @enderror"
                                                    placeholder="Ej: F-150">
                                                @error('formModelo')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Vehículo</label>
                                                <input type="text" wire:model="formVehiculo"
                                                    class="form-control form-control-sm @error('formVehiculo') is-invalid @enderror"
                                                    placeholder="Identificador interno">
                                                @error('formVehiculo')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nº Chasis</label>
                                                <input type="text" wire:model="formNumeroChasis"
                                                    class="form-control form-control-sm @error('formNumeroChasis') is-invalid @enderror"
                                                    placeholder="Número de chasis">
                                                @error('formNumeroChasis')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nº Motor</label>
                                                <input type="text" wire:model="formNumeroMotor"
                                                    class="form-control form-control-sm @error('formNumeroMotor') is-invalid @enderror"
                                                    placeholder="Número de motor">
                                                @error('formNumeroMotor')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Ejes <span class="text-danger">*</span></label>
                                                <input type="number" wire:model="formEjes" min="1"
                                                    max="10"
                                                    class="form-control form-control-sm @error('formEjes') is-invalid @enderror">
                                                @error('formEjes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Estado <span class="text-danger">*</span></label>
                                                <select wire:model="formEstado"
                                                    class="form-control form-control-sm @error('formEstado') is-invalid @enderror">
                                                    <option value="verde">Verde — OK</option>
                                                    <option value="amarillo">Amarillo — Observación</option>
                                                    <option value="rojo">Rojo — Fuera de servicio</option>
                                                    <option value="negro">Negro — Dado de baja</option>
                                                </select>
                                                @error('formEstado')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Descripción</label>
                                                <textarea wire:model="formDescripcion" rows="2"
                                                    class="form-control form-control-sm @error('formDescripcion') is-invalid @enderror"
                                                    placeholder="Observaciones sobre el vehículo..."></textarea>
                                                @error('formDescripcion')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </x-ops-card>

                                {{-- ═══ SECCIÓN 2: CATÁLOGOS ═══ --}}
                                <x-ops-card title="Catálogos" icon="th-list" class="mt-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Unidad</label>
                                                <select wire:model="formUnidadId"
                                                    class="form-control form-control-sm @error('formUnidadId') is-invalid @enderror">
                                                    <option value="">-- Seleccionar --</option>
                                                    @foreach ($catalogos['unidades']->where('nombre', '!=', 'C.A.C.O.') as $unidad)
                                                        <option value="{{ $unidad->id }}">{{ $unidad->nombre}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('formUnidadId')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Tipo de Vehículo</label>
                                                <select wire:model="formTipoVehiculoId"
                                                    class="form-control form-control-sm @error('formTipoVehiculoId') is-invalid @enderror">
                                                    <option value="">-- Seleccionar --</option>
                                                    @foreach ($catalogos['tiposVehiculo'] as $tipo)
                                                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('formTipoVehiculoId')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Tipo de Combustible <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-sm">
                                                    <select wire:model="formTipoCombustibleId"
                                                        class="form-control @error('formTipoCombustibleId') is-invalid @enderror">
                                                        <option value="">-- Seleccionar --</option>
                                                        @foreach ($catalogos['tiposCombustible'] as $tipo)
                                                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        @livewire('catalogos.tipos-combustible-modal', key('combustible-modal-vehiculo'))
                                                    </div>
                                                </div>
                                                @error('formTipoCombustibleId')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Tipo de Lubricante</label>
                                                <div class="input-group input-group-sm">
                                                    <select wire:model="formTipoLubricanteId"
                                                        class="form-control @error('formTipoLubricanteId') is-invalid @enderror">
                                                        <option value="">-- Seleccionar --</option>
                                                        @foreach ($catalogos['tiposLubricante'] as $tipo)
                                                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        @livewire('catalogos.tipos-lubricante-modal', key('lubricante-modal-vehiculo'))
                                                    </div>
                                                </div>
                                                @error('formTipoLubricanteId')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Tipo de Rodado</label>
                                                <div class="input-group input-group-sm">
                                                    <select wire:model="formTipoRodadoId"
                                                        class="form-control @error('formTipoRodadoId') is-invalid @enderror">
                                                        <option value="">-- Seleccionar --</option>
                                                        @foreach ($catalogos['tiposRodado'] as $tipo)
                                                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        @livewire('catalogos.tipos-rodado-modal', key('rodado-modal-vehiculo'))
                                                    </div>
                                                </div>
                                                @error('formTipoRodadoId')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </x-ops-card>

                                {{-- ═══ SECCIÓN 3: TÉCNICOS ═══ --}}
                                <x-ops-card title="Técnicos" icon="cogs" class="mt-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Consumo (L/km)</label>
                                                <input type="number" wire:model="formConsumoLitrosPorKm"
                                                    step="0.0001" min="0" max="999.9999"
                                                    class="form-control form-control-sm @error('formConsumoLitrosPorKm') is-invalid @enderror"
                                                    placeholder="Ej: 0.1500">
                                                @error('formConsumoLitrosPorKm')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group pt-4">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="formSinCuentakilometros"
                                                        wire:model="formSinCuentakilometros">
                                                    <label class="custom-control-label"
                                                        for="formSinCuentakilometros">Sin cuenta kilómetros</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group pt-4">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="formActivo" wire:model="formActivo"
                                                        @if (in_array($formEstado, ['rojo', 'negro'])) disabled @endif>
                                                    <label class="custom-control-label"
                                                        for="formActivo">Activo</label>
                                                    @if (in_array($formEstado, ['rojo', 'negro']))
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="fas fa-info-circle"></i> Se desactiva
                                                            automáticamente porque el estado es {{ $formEstado }}.
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </x-ops-card>

                                {{-- ═══ SECCIÓN 4: DOCUMENTACIÓN ═══
                                 Todo el estado real (archivos en cola, tamaños, nombres) vive
                                 en el servidor ($queuedActaPaths). No hay sincronización manual
                                 por Alpine: cada acción pasa por un método de Livewire y la
                                 vista se re-renderiza sola. Lo único client-side es el chequeo
                                 de tamaño ANTES de subir, para no colgar la UI con archivos
                                 grandes. --}}
                                <x-ops-card title="Documentación" icon="file-alt" class="mt-3">
                                    @error('singleActaUpload')
                                        <div class="alert alert-danger alert-sm mb-3">
                                            <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                    @error('queuedActaPaths')
                                        <div class="alert alert-danger alert-sm mb-3">
                                            <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                        </div>
                                    @enderror

                                    {{-- Lista de archivos existentes (solo en edición) --}}
                                    @if ($formTipo === 'edit' && !empty($formActasExistentes))
                                        <div class="mb-3">
                                            <strong>Archivos registrados:</strong>
                                            <div class="table-responsive mt-2">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>Tamaño</th>
                                                            <th class="text-center" style="width: 80px">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($formActasExistentes as $acta)
                                                            <tr wire:key="acta-{{ $acta['id'] }}">
                                                                <td>
                                                                    <a href="{{ Storage::url($acta['path']) }}"
                                                                        target="_blank">
                                                                        <i class="fas fa-file-pdf"></i>
                                                                        {{ $acta['nombre_original'] }}
                                                                    </a>
                                                                </td>
                                                                <td>{{ round($acta['tamano_bytes'] / 1048576, 2) }} MB
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        wire:click="eliminarActaExistente({{ $acta['id'] }})"
                                                                        wire:confirm="¿Eliminar este archivo? Esta acción no se puede deshacer."
                                                                        title="Eliminar archivo">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Archivos en cola (nuevos, no guardados aún en BD) --}}
                                    @if (!empty($queuedActaPaths))
                                        <div class="mb-3">
                                            <strong>Archivos pendientes de guardar:</strong>
                                            <div class="table-responsive mt-2">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>Tamaño</th>
                                                            <th class="text-center" style="width: 80px">Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($queuedActaPaths as $index => $acta)
                                                            <tr
                                                                wire:key="queued-{{ $index }}-{{ $acta['path'] }}">
                                                                <td>
                                                                    <i class="fas fa-file-pdf"></i>
                                                                    {{ $acta['nombre_original'] }}
                                                                </td>
                                                                <td>{{ round($acta['tamano_bytes'] / 1048576, 2) }} MB
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        wire:click="eliminarActaEnCola({{ $index }})"
                                                                        title="Eliminar de la cola">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Botón para agregar archivo.
                                     El input NO usa wire:model: se sube manualmente con
                                     $wire.upload(), que da callbacks de éxito/error propios.
                                     Antes de llamar a $wire.upload validamos el tamaño en el
                                     navegador, así un archivo de 30MB se rechaza al instante
                                     sin transferir un solo byte. --}}
                                    <div class="row" x-data="{ subiendo: false, errorLocal: null }">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-sm btn-outline-primary mb-2"
                                                :disabled="subiendo" x-on:click="$refs.fileInput.click()">
                                                <template x-if="subiendo">
                                                    <span><span class="spinner-border spinner-border-sm mr-1"></span>
                                                        Subiendo...</span>
                                                </template>
                                                <template x-if="!subiendo">
                                                    <span><i class="fas fa-plus"></i> Agregar archivo</span>
                                                </template>
                                            </button>
                                            <input type="file" x-ref="fileInput" hidden
                                                accept=".pdf,.jpg,.jpeg,.png,.bmp,.doc,.docx"
                                                x-on:change="
                                                errorLocal = null;
                                                const file = $event.target.files[0];
                                                if (!file) return;
                                                const maxBytes = 10 * 1024 * 1024;
                                                if (file.size > maxBytes) {
                                                    errorLocal = 'El archivo pesa ' + (file.size / 1048576).toFixed(2) + 'MB. El máximo por archivo es 10MB.';
                                                    $refs.fileInput.value = '';
                                                    return;
                                                }
                                                subiendo = true;
                                                $wire.upload('singleActaUpload', file,
                                                    () => { subiendo = false; $refs.fileInput.value = ''; },
                                                    () => { subiendo = false; errorLocal = 'Error al subir el archivo. Probá de nuevo.'; $refs.fileInput.value = ''; }
                                                );
                                            ">
                                            <small class="text-muted d-block">Máximo 5 archivos, 10MB en total entre
                                                todos. Formatos: PDF, imágenes, DOC.</small>
                                            <small class="text-danger d-block mt-1" x-show="errorLocal"
                                                x-text="errorLocal"></small>
                                        </div>
                                    </div>
                                </x-ops-card>

                            </form>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cerrarForm"
                            wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                            Cancelar
                        </button>
                        <button type="submit" form="form-vehiculo" class="btn btn-ops-primary"
                            wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span>
                            @endif
                            <i class="fas fa-save"></i>
                            {{ $formTipo === 'create' ? 'Crear Vehículo' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL: DETALLE / SHOW (ops-panel overlay) --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalDetalleVehiculo" x-data x-init="$watch('$wire.showDetalle', value => {
            if (value) document.body.classList.add('ops-panel-open');
            else document.body.classList.remove('ops-panel-open');
        })"
            :class="{ 'is-open': $wire.showDetalle }" wire:click.self="cerrarDetalle">
            <div class="ops-panel ops-panel--vehiculo">
                <div class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">BCOM1 · Detalle</span>
                            <h5 class="ops-panel__title">
                                Vehículo: <strong>{{ $detalleVehiculo?->matricula ?? '' }}</strong>
                            </h5>
                        </div>
                        <button type="button" class="ops-panel__close" wire:click="cerrarDetalle" title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body">
                        <div class="ops-panel__content">
                            @if ($detalleVehiculo)
                                {{-- Pestañas --}}
                                <ul class="nav nav-tabs nav-tabs-ops mb-3" id="detalleTabs">
                                    <li class="nav-item">
                                        <a class="nav-link nav-link-ops active" data-toggle="tab"
                                            href="#detalleInfo">
                                            <i class="fas fa-truck"></i> Información
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link nav-link-ops" data-toggle="tab" href="#detalleSalidas">
                                            <i class="fas fa-route"></i> Últimas Salidas
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link nav-link-ops" data-toggle="tab"
                                            href="#detalleMantenimientos">
                                            <i class="fas fa-wrench"></i> Mantenimientos
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    {{-- TAB: Información --}}
                                    <div class="tab-pane active" id="detalleInfo">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="conductor-section">
                                                    <div class="conductor-section__title">
                                                        <i class="fas fa-info-circle"></i> Datos Generales
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-id-card"></i> Matrícula</span>
                                                        <span
                                                            class="conductor-field__value fw-bold">{{ $detalleVehiculo->matricula }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-truck"></i> Marca / Modelo</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->marca ?? '—' }}
                                                            {{ $detalleVehiculo->modelo ?? '' }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-hashtag"></i> Vehículo</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->vehiculo ?? '—' }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-barcode"></i> Nº Chasis</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->numero_chasis ?? '—' }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-cog"></i> Nº Motor</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->numero_motor ?? '—' }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-arrows-alt-v"></i> Ejes</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->ejes }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-flag"></i> Estado</span>
                                                        <span class="conductor-field__value">
                                                            <span
                                                                class="{{ $detalleVehiculo->estado_badge_class }}">{{ $detalleVehiculo->estado_label }}</span>
                                                        </span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-check-circle"></i> Activo</span>
                                                        <span class="conductor-field__value">
                                                            @if ($detalleVehiculo->activo)
                                                                <span class="badge-ops badge-ops-success">Sí</span>
                                                            @else
                                                                <span class="badge-ops badge-ops-secondary">No</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    @if ($detalleVehiculo->descripcion)
                                                        <div class="conductor-field">
                                                            <span class="conductor-field__label"><i
                                                                    class="fas fa-align-left"></i> Descripción</span>
                                                            <span
                                                                class="conductor-field__value">{{ $detalleVehiculo->descripcion }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="conductor-section">
                                                    <div class="conductor-section__title">
                                                        <i class="fas fa-th-list"></i> Catálogos
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-building"></i> Unidad</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->unidad->nombre ?? '—' }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-truck"></i> Tipo Vehículo</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->tipoVehiculo->nombre ?? '—' }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-gas-pump"></i> Tipo Combustible</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->tipoCombustible->nombre ?? '—' }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-oil-can"></i> Tipo Lubricante</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->tipoLubricante->nombre ?? '—' }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-circle"></i> Tipo Rodado</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->tipoRodado->nombre ?? '—' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="conductor-section">
                                                    <div class="conductor-section__title">
                                                        <i class="fas fa-cogs"></i> Datos Técnicos
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-tachometer-alt"></i> Consumo
                                                            (L/km)</span>
                                                        <span
                                                            class="conductor-field__value">{{ $detalleVehiculo->consumo_litros_por_km ? number_format($detalleVehiculo->consumo_litros_por_km, 4) : '—' }}</span>
                                                    </div>
                                                    <div class="conductor-field">
                                                        <span class="conductor-field__label"><i
                                                                class="fas fa-tachometer-alt"></i> Sin cuenta
                                                            kilómetros</span>
                                                        <span class="conductor-field__value">
                                                            @if ($detalleVehiculo->sin_cuentakilometros)
                                                                <span class="badge-ops badge-ops-success">Sí</span>
                                                            @else
                                                                <span class="badge-ops badge-ops-secondary">No</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="conductor-section">
                                                    <div class="conductor-section__title">
                                                        <i class="fas fa-file-alt"></i> Documentación
                                                    </div>
                                                    @if ($detalleVehiculo->actas && $detalleVehiculo->actas->count() > 0)
                                                        @foreach ($detalleVehiculo->actas as $acta)
                                                            <div class="conductor-field">
                                                                <span class="conductor-field__label"><i
                                                                        class="fas fa-file-pdf"></i>
                                                                    {{ $acta->nombre_original }}</span>
                                                                <span class="conductor-field__value">
                                                                    <a href="{{ Storage::url($acta->path) }}"
                                                                        target="_blank">
                                                                        <i class="fas fa-download"></i> Descargar
                                                                        ({{ number_format($acta->tamano_bytes / 1048576, 2) }}
                                                                        MB)
                                                                    </a>
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="conductor-field">
                                                            <span class="conductor-field__value text-muted">Sin actas
                                                                registradas.</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- TAB: Últimas Salidas --}}
                                    <div class="tab-pane" id="detalleSalidas">
                                        @if ($detalleVehiculo->salidas && $detalleVehiculo->salidas->count() > 0)
                                            @foreach ($detalleVehiculo->salidas as $salida)
                                                <div class="salida-card">
                                                    <div
                                                        class="salida-card__icon {{ $salida->estado === 'cerrada' ? 'salida-card__icon--cerrada' : 'salida-card__icon--pendiente' }}">
                                                        <i
                                                            class="fas {{ $salida->estado === 'cerrada' ? 'fa-check' : 'fa-clock' }}"></i>
                                                    </div>
                                                    <div class="salida-card__info">
                                                        <h6>{{ $salida->conductor?->primer_apellido ?? 'N/A' }}{{ $salida->conductor?->primer_nombre ?? '' }}
                                                        </h6>
                                                        <small>
                                                            <i class="fas fa-calendar"></i>
                                                            {{ $salida->guardia?->date?->format('d/m/Y') ?? '—' }}
                                                            &nbsp;·&nbsp;
                                                            <i class="fas fa-clock"></i>
                                                            Sale:
                                                            {{ $salida->hora_sale ? $salida->hora_sale->format('H:i') : '—' }}
                                                            @if ($salida->hora_entra)
                                                                &nbsp;·&nbsp; Entra:
                                                                {{ $salida->hora_entra->format('H:i') }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                    <div class="salida-card__meta">
                                                        <small>Kms</small>
                                                        <strong>{{ $salida->kms_recorridos ?? '—' }}</strong>
                                                    </div>
                                                    <span
                                                        class="salida-card__badge {{ $salida->estado === 'cerrada' ? 'badge-ops-success' : 'badge-ops-danger' }}">
                                                        {{ $salida->estado === 'cerrada' ? 'Cerrada' : 'Pendiente' }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-4">
                                                <i class="fas fa-route fa-2x text-muted d-block mb-2"></i>
                                                <p class="text-muted mb-0">No se registran salidas recientes para este
                                                    vehículo.</p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- TAB: Mantenimientos --}}
                                    <div class="tab-pane" id="detalleMantenimientos">
                                        @livewire('vehiculos.mantenimiento-modal', ['vehiculo' => $detalleVehiculo], key('mant-modal-' . $detalleVehiculo->id))
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <div style="display: flex; justify-content: flex-end; gap: .5rem;">
                            <button type="button" class="btn btn-outline-secondary" wire:click="cerrarDetalle">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                            @if ($detalleVehiculo && auth()->user()->can('update', $detalleVehiculo))
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
                        <p>¿Estás seguro de que deseas eliminar este vehículo?</p>
                        <p class="text-muted mb-0"><small>Esta acción eliminará también los archivos de acta asociados
                                si existen.</small></p>
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

    {{-- MODAL: CONFIRMAR ELIMINACIÓN PERMANENTE (papelera) --}}
    @if ($confirmForceDeleteId)
        <div class="modal fade show d-block" tabindex="-1"
            style="background: rgba(255, 255, 255, 0.15) !important; backdrop-filter: blur(12px) saturate(180%) !important; -webkit-backdrop-filter: blur(12px) saturate(180%) !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; border-radius: 16px !important; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;"
            wire:click.self="$set('confirmForceDeleteId', null)"
            wire:keydown.escape="$set('confirmForceDeleteId', null)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Eliminación permanente
                        </h5>
                        <button type="button" class="close text-white"
                            wire:click="$set('confirmForceDeleteId', null)">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Esta acción es irreversible y eliminará también todos los archivos adjuntos (actas)
                                de este vehículo.</strong></p>
                        <p class="text-muted mb-0"><small>¿Confirmás que querés continuar?</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('confirmForceDeleteId', null)">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="ejecutarEliminacionPermanente"
                            @disabled($loading)>
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span> Eliminando...
                            @else
                                <i class="fas fa-trash"></i> Eliminar permanentemente
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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