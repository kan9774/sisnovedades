<div>
    {{-- ALERTAS GLOBALES --}}
    @if ($successMsg)
        <div wire:key="success-{{ md5($successMsg) }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.set('successMsg', '')
        }, 4000)"
            x-transition class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ $successMsg }}
            <button type="button" class="close" wire:click="$set('successMsg', '')">&times;</button>
        </div>
    @endif

    @if ($errorMsg)
        <div wire:key="error-{{ md5($errorMsg) }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.set('errorMsg', '')
        }, 5000)"
            x-transition class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ $errorMsg }}
            <button type="button" class="close" wire:click="$set('errorMsg', '')">&times;</button>
        </div>
    @endif

    <x-ops-card title="Permisos" icon="shield" eyebrow="{{ $permisos->total() }} registros">
        <x-slot name="actions">
            @can('create', \App\Models\Permission::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                    Nuevo Permiso
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
                        placeholder="Buscar por nombre o descripción...">
                </div>
            </div>
            <div class="col-md-4 text-right">
                @if ($search)
                    <button wire:click="clearFilters" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-times"></i> Limpiar filtros
                    </button>
                @endif
            </div>
        </div>

        @if ($loading && !$showForm)
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Cargando permisos...</p>
            </div>
        @else
            {{-- TABLA --}}
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th style="width: 30%">Nombre</th>
                            <th style="width: 35%">Descripción</th>
                            <th style="width: 15%">Módulo</th>
                            <th style="width: 10%" class="text-center">Roles</th>
                            <th style="width: 10%" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permisos as $permiso)
                            <tr wire:key="permiso-{{ $permiso->id }}">
                                <td>
                                    <code>{{ $permiso->name }}</code>
                                </td>
                                <td class="text-muted">
                                    {{ $permiso->description ?? '—' }}
                                </td>
                                <td>
                                    @if ($permiso->model)
                                        <span class="badge bg-info">{{ $permiso->model }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $permiso->rols_count }}</span>
                                </td>
                                <td class="text-end">
                                    @can('update', $permiso)
                                        <x-btn-ops variant="warning" icon="pen"
                                            wire:click="abrirEditar({{ $permiso->id }})"
                                            size="xs" title="Editar">
                                        </x-btn-ops>
                                    @endcan
                                    @can('delete', $permiso)
                                        <x-btn-ops variant="danger" icon="trash"
                                            wire:click="confirmDelete({{ $permiso->id }})"
                                            size="xs" title="Eliminar">
                                        </x-btn-ops>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay permisos cargados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN --}}
            @if ($permisos->hasPages())
                <div class="card-footer bg-white border-0 pt-3">
                    {{ $permisos->links() }}
                </div>
            @endif
        @endif
    </x-ops-card>

    {{-- MODAL: FORMULARIO CREAR / EDITAR (ops-panel overlay) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalPermisos"
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
                            {{ $formTipo === 'create' ? 'Nuevo Permiso' : 'Editar Permiso' }}
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" wire:click="cerrarForm"
                        title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body" wire:loading.class="opacity-50" wire:target="submitForm">
                    <div class="ops-panel__content">
                        @if ($justSaved)
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h5 class="text-success">{{ $successMsg }}</h5>
                            </div>
                        @else
                            @if ($errorMsg)
                                <div class="alert alert-danger">{{ $errorMsg }}</div>
                            @endif
                            <form wire:submit="submitForm" id="form-permiso">
                                <div class="form-group">
                                    <label>Nombre <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live="formNombre"
                                        class="form-control @error('formNombre') is-invalid @enderror"
                                        placeholder="Ej: ver_oficinas">
                                    @error('formNombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Descripción</label>
                                    <input type="text" wire:model="formDescripcion"
                                        class="form-control @error('formDescripcion') is-invalid @enderror"
                                        placeholder="Descripción opcional...">
                                    @error('formDescripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Módulo (modelo)</label>
                                    <select wire:model.live="formModel"
                                        class="form-control @error('formModel') is-invalid @enderror">
                                        <option value="">Sin módulo</option>
                                        @foreach ($modulos as $modulo)
                                            <option value="{{ $modulo }}">{{ $modulo }}</option>
                                        @endforeach
                                        <option value="" disabled>— u otro —</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        El módulo se agrupa automáticamente por Rol. Podés escribir un valor nuevo.
                                    </small>
                                    @error('formModel')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="ops-panel__footer">
                    @if (!$justSaved)
                        <button type="button" class="btn btn-outline-secondary"
                            wire:click="cerrarForm"
                            wire:loading.attr="disabled" wire:target="submitForm" @disabled($loading)>
                            Cancelar
                        </button>
                        <button type="submit" form="form-permiso" class="btn btn-ops-primary"
                            wire:loading.attr="disabled" wire:target="submitForm" @disabled($loading)>
                            <i class="fas fa-save"></i>
                            {{ $formTipo === 'create' ? 'Crear' : 'Guardar' }}
                        </button>
                    @endif
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
                        <p>¿Estás seguro de que deseas eliminar este permiso?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('confirmDeleteId', null)">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="executeDelete"
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
</div>
