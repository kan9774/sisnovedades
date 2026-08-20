<div>
    <x-ops-card title="Guardias" icon="shield-alt" eyebrow="{{ $guardias->total() ?? 0 }} registros">
        <x-slot name="actions">
            @if ($viewMode === 'papelera')
                <button wire:click="verActivos" class="btn-ops btn-ops-secondary btn-sm me-1"
                    data-toggle="tooltip" title="Ver guardias activas">
                    <i class="fas fa-arrow-left"></i> Activos
                </button>
            @else
                @can('viewTrashed', \App\Models\Guard::class)
                    <button wire:click="verPapelera" class="btn-ops btn-ops-secondary btn-sm me-1"
                        data-toggle="tooltip" title="Ver guardias eliminadas">
                        <i class="fas fa-trash-alt"></i> Papelera
                    </button>
                @endcan
            @endif

            @if ($viewMode === 'activos')
                @can('create', \App\Models\Guard::class)
                    <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                        Nueva Guardia
                    </x-btn-ops>
                @endcan
            @endif
        </x-slot>

        {{-- INDICADOR DE VISTA --}}
        @if ($viewMode === 'papelera')
            <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Papelera:</strong> Mostrando guardias eliminadas. Puedes restaurarlas o eliminarlas permanentemente.
                <button type="button" class="close" wire:click="verActivos">&times;</button>
            </div>
        @endif

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
                        placeholder="@if ($viewMode === 'papelera') Buscar en guardias eliminadas... @else Buscar por capitán, oficial, escribiente o fecha... @endif">
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
                        <th>Fecha</th>
                        <th>Capitán</th>
                        <th>Oficial de Día</th>
                        <th>Escribiente</th>
                        <th>Estado</th>
                        <th class="text-center">Novedades</th>
                        @if ($viewMode === 'papelera')
                            <th class="text-center">Eliminado</th>
                        @endif
                        <th class="text-center" style="width: 180px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guardias as $guardia)
                        <tr wire:key="guardia-{{ $guardia->id }}">
                            <td class="fw-bold">
                                <i class="far fa-calendar-alt text-primary me-1"></i>
                                {{ $guardia->date->format('d/m/Y') }}
                            </td>
                            <td>{{ $guardia->capitan->name }} {{ $guardia->capitan->last_name }}</td>
                            <td>{{ $guardia->oficial->name }} {{ $guardia->oficial->last_name }}</td>
                            <td>
                                @php $escribiente = $guardia->escribiente->first(); @endphp
                                {{ $escribiente?->name }} {{ $escribiente?->last_name }}
                            </td>
                            <td>
                                @if ($guardia->status === 'open')
                                    <span class="badge-ops badge-ops-success">Abierta</span>
                                @else
                                    <span class="badge-ops badge-ops-danger">Cerrada</span>
                                @endif
                            </td>
                            @if ($viewMode === 'papelera')
                                <td class="text-center text-muted">
                                    <i class="fas fa-trash text-muted me-1"></i>
                                    {{ $guardia->deleted_at->format('d/m/Y H:i') }}
                                </td>
                            @endif
                            <td class="text-center">
                                {{ $guardia->novedades_count ?? 0 }}
                            </td>
                            <td class="text-center align-middle">
                                <div class="ops-actions justify-content-center">
                                    @if ($viewMode === 'activos')
                                        {{-- Botón Ver --}}
                                        <a href="{{ route('admin.guardias.show', $guardia) }}"
                                            class="btn-ops btn-ops-info btn-sm mr-1" data-toggle="tooltip"
                                            title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Botón Editar --}}
                                        @can('update', $guardia)
                                            <x-btn-ops variant="warning" icon="pen"
                                                wire:click="abrirEditar({{ $guardia->id }})" size="xs"
                                                title="Editar"></x-btn-ops>
                                        @endcan

                                        {{-- Botón Eliminar --}}
                                        @can('delete', $guardia)
                                            <x-btn-ops variant="danger" icon="trash"
                                                wire:click="confirmarEliminacion({{ $guardia->id }})" size="xs"
                                                title="Eliminar"></x-btn-ops>
                                        @endcan
                                    @else
                                        {{-- Papelera: Restaurar --}}
                                        @can('restore', $guardia)
                                            <x-btn-ops variant="success" icon="undo"
                                                wire:click="restaurar({{ $guardia->id }})" size="xs"
                                                title="Restaurar"></x-btn-ops>
                                        @endcan

                                        {{-- Papelera: Eliminar permanentemente --}}
                                        @can('forceDelete', $guardia)
                                            <x-btn-ops variant="danger" icon="trash"
                                                wire:click="confirmarEliminacionPermanente({{ $guardia->id }})" size="xs"
                                                title="Eliminar permanentemente"></x-btn-ops>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="@if ($viewMode === 'papelera') 7 @else 6 @endif" class="text-center text-muted py-4">
                                @if ($viewMode === 'papelera')
                                    <i class="fas fa-trash fa-2x d-block mb-2"></i>
                                    No hay guardias en la papelera.
                                @else
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No hay guardias registradas.
                                    @can('create', \App\Models\Guard::class)
                                        <br>
                                        <button wire:click="crear" class="btn-ops btn-ops-primary btn-sm mt-2">
                                            <i class="fas fa-plus-circle"></i> Crear la primera
                                        </button>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($guardias->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $guardias->links() }}
            </div>
        @endif
    </x-ops-card>

    {{-- MODAL: FORMULARIO CREAR / EDITAR (ops-panel overlay) --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalGuardia" x-data x-init="$watch('$wire.showForm', value => {
            if (value) document.body.classList.add('ops-panel-open');
            else document.body.classList.remove('ops-panel-open');
        })"
            :class="{ 'is-open': $wire.showForm }" wire:click.self="cerrarForm">
            <div class="ops-panel">
                <div class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">BCOM1 · Guardia</span>
                            <h5 class="ops-panel__title">
                                @if ($formTipo === 'create')
                                    Nueva Guardia
                                @else
                                    Editar Guardia
                                @endif
                            </h5>
                        </div>
                        <button type="button" class="ops-panel__close" wire:click="cerrarForm" title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body" wire:loading.class="opacity-50" wire:target="guardar">
                        <div class="ops-panel__content">
                            <form wire:submit="guardar" id="form-guardia">

                                {{-- ═══ FECHA ═══ --}}
                                <x-ops-card title="Fecha" icon="calendar-alt">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Fecha <span class="text-danger">*</span></label>
                                                @if ($formTipo === 'edit')
                                                    <input type="text" class="form-control"
                                                        value="{{ $formDateDisplay }}"
                                                        disabled>
                                                    @error('formDate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                @else
                                                    <input type="date" wire:model="formDate"
                                                        class="form-control @error('formDate') is-invalid @enderror">
                                                    @error('formDate')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </x-ops-card>

                                {{-- ═══ PERSONAL ═══ --}}
                                <x-ops-card title="Personal Asignado" icon="users" class="mt-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Capitán de Servicio <span class="text-danger">*</span></label>
                                                <select wire:model="formCaptainId"
                                                    class="form-control @error('formCaptainId') is-invalid @enderror">
                                                    <option value="" disabled>-- Seleccionar Capitán --</option>
                                                    @foreach ($catalogos['capitanes'] as $capitan)
                                                        <option value="{{ $capitan->id }}"
                                                            {{ old('formCaptainId', $formCaptainId) == $capitan->id ? 'selected' : '' }}>
                                                            {{ $capitan->grade }} {{ $capitan->name }} {{ $capitan->last_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('formCaptainId')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Oficial de Día <span class="text-danger">*</span></label>
                                                <select wire:model="formOficerId"
                                                    class="form-control @error('formOficerId') is-invalid @enderror">
                                                    <option value="" disabled>-- Seleccionar Oficial --</option>
                                                    @foreach ($catalogos['oficiales'] as $oficial)
                                                        <option value="{{ $oficial->id }}"
                                                            {{ old('formOficerId', $formOficerId) == $oficial->id ? 'selected' : '' }}>
                                                            {{ $oficial->grade }} {{ $oficial->name }} {{ $oficial->last_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('formOficerId')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Escribiente <span class="text-danger">*</span></label>
                                                @if (Auth::user()->isEscribiente() && $formTipo === 'create')
                                                    <input type="text" class="form-control"
                                                        value="{{ Auth::user()->grade }} {{ Auth::user()->name }} {{ Auth::user()->last_name }}"
                                                        disabled>
                                                    <input type="hidden" wire:model="formEscribienteId"
                                                        value="{{ Auth::id() }}">
                                                @else
                                                    <select wire:model="formEscribienteId"
                                                        class="form-control @error('formEscribienteId') is-invalid @enderror">
                                                        <option value="" disabled>-- Seleccionar Escribiente --</option>
                                                        @foreach ($catalogos['escribientes'] as $escribiente)
                                                            <option value="{{ $escribiente->id }}"
                                                                {{ old('formEscribienteId', $formEscribienteId) == $escribiente->id ? 'selected' : '' }}>
                                                                {{ $escribiente->grade }} {{ $escribiente->name }} {{ $escribiente->last_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('formEscribienteId')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </x-ops-card>

                                {{-- ═══ NOTAS ═══ --}}
                                <x-ops-card title="Notas" icon="sticky-note" class="mt-3">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Notas <small class="text-muted">(opcional)</small></label>
                                                <textarea wire:model="formNotes" rows="3"
                                                    class="form-control @error('formNotes') is-invalid @enderror"
                                                    placeholder="Observaciones adicionales..."></textarea>
                                                @error('formNotes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </x-ops-card>

                            </form>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cerrarForm"
                            wire:loading.attr="disabled" wire:target="guardar">
                            Cancelar
                        </button>
                        <button type="submit" form="form-guardia" class="btn btn-ops-primary"
                            wire:loading.attr="disabled" wire:target="guardar">
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span>
                            @endif
                            <i class="fas fa-save"></i>
                            {{ $formTipo === 'create' ? 'Abrir Guardia' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL: CONFIRMAR ELIMINACIÓN (ops-panel overlay) --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalDeleteGuardia" x-data x-init="$watch('$wire.confirmDeleteId', value => {
            if (value) document.body.classList.add('ops-panel-open');
            else document.body.classList.remove('ops-panel-open');
        })"
            :class="{ 'is-open': $wire.confirmDeleteId !== null }"
            wire:click.self="confirmDeleteId = null">
            <div class="ops-panel ops-panel--sm">
                <div class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">Confirmar</span>
                            <h5 class="ops-panel__title">Eliminar Guardia</h5>
                        </div>
                        <button type="button" class="ops-panel__close" wire:click="confirmDeleteId = null" title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body">
                        <div class="ops-panel__content">
                            <div class="text-center mb-3">
                                <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                            </div>
                            <p class="text-center mb-0">
                                ¿Está seguro que desea eliminar esta guardia? Será movida a la papelera y podrá ser restaurada posteriormente.
                            </p>
                            <p class="text-muted small text-center mt-2">
                                Esta acción solo puede ser realizada por Super Administradores.
                            </p>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <button type="button" class="btn btn-outline-secondary"
                            wire:click="confirmDeleteId = null" wire:loading.attr="disabled">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-ops-danger"
                            wire:click="ejecutarEliminacion"
                            wire:loading.attr="disabled">
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span>
                            @endif
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL: CONFIRMAR ELIMINACIÓN PERMANENTE (papelera) --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalForceDeleteGuardia" x-data x-init="$watch('$wire.guardiaAEliminarId', value => {
            if (value) document.body.classList.add('ops-panel-open');
            else document.body.classList.remove('ops-panel-open');
        })"
            :class="{ 'is-open': $wire.guardiaAEliminarId !== null }"
            wire:click.self="guardiaAEliminarId = null">
            <div class="ops-panel ops-panel--sm">
                <div class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">Peligro</span>
                            <h5 class="ops-panel__title">Eliminar Permanentemente</h5>
                        </div>
                        <button type="button" class="ops-panel__close" wire:click="guardiaAEliminarId = null" title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body">
                        <div class="ops-panel__content">
                            <div class="text-center mb-3">
                                <i class="fas fa-skull-crossbones fa-3x text-danger"></i>
                            </div>
                            <p class="text-center mb-0 fw-bold text-danger">
                                ¿Está seguro que desea eliminar permanentemente esta guardia?
                            </p>
                            <p class="text-center mt-3">
                                Esta acción <strong>no se puede deshacer</strong> y eliminará en cascada:
                            </p>
                            <ul class="text-muted small text-center mt-2 mb-0">
                                <li>Todas las novedades asociadas</li>
                                <li>Todos los adjuntos/archivos almacenados en disco</li>
                                <li>Las salidas de vehículo registradas</li>
                                <li>Los escribientes vinculados</li>
                            </ul>
                            <p class="text-danger small text-center mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle"></i> Solo Super Administradores pueden realizar esta acción.
                            </p>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <button type="button" class="btn btn-outline-secondary"
                            wire:click="guardiaAEliminarId = null" wire:loading.attr="disabled">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-ops-danger"
                            wire:click="ejecutarEliminacionPermanente"
                            wire:loading.attr="disabled">
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span>
                            @endif
                            <i class="fas fa-trash"></i> Eliminar Permanentemente
                        </button>
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