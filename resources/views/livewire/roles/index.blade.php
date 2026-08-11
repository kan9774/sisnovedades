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

    <x-ops-card title="Roles" icon="shield-halved" eyebrow="{{ $roles->count() }} registros">
        <x-slot name="actions">
            @can('create', \App\Models\Rol::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                    Nuevo Rol
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
                <p class="mt-2 text-muted">Cargando roles...</p>
            </div>
        @else
            {{-- TABLA --}}
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th style="width: 25%">Nombre</th>
                            <th style="width: 35%">Descripción</th>
                            <th style="width: 10%" class="text-center">Usuarios</th>
                            <th style="width: 10%" class="text-center">Permisos</th>
                            <th style="width: 20%" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $rol)
                            <tr wire:key="rol-{{ $rol->id }}">
                                <td>
                                    <strong>{{ $rol->name }}</strong>
                                    @if ($rol->name === 'admin')
                                        <span class="badge bg-danger ms-1">admin</span>
                                    @endif
                                </td>
                                <td class="text-muted">
                                    {{ $rol->description ?? '—' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $rol->users_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $rol->permisos->count() }}</span>
                                </td>
                                <td class="text-end">
                                    @can('update', $rol)
                                        <x-btn-ops variant="warning" icon="pen"
                                            wire:click="abrirEditar({{ $rol->id }})"
                                            size="xs" title="Editar">
                                        </x-btn-ops>
                                    @endcan
                                    @can('delete', $rol)
                                        <x-btn-ops variant="danger" icon="trash"
                                            wire:click="confirmDelete({{ $rol->id }})"
                                            size="xs" title="Eliminar">
                                        </x-btn-ops>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay roles cargados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </x-ops-card>

    {{-- MODAL: FORMULARIO CREAR / EDITAR (ops-panel overlay) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalRoles"
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
                            {{ $formTipo === 'create' ? 'Nuevo Rol' : 'Editar Rol' }}
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
                            <form wire:submit="submitForm" id="form-rol">
                                <div class="form-group">
                                    <label>Nombre <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live="formNombre"
                                        class="form-control @error('formNombre') is-invalid @enderror"
                                        placeholder="Ej: capitan_de_servicio">
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

                                <hr>

                                <h6 class="mb-3"><i class="fas fa-shield-alt"></i> Permisos</h6>
                                <p class="text-muted small mb-3">
                                    Seleccioná los permisos que tendrá este rol.
                                    @if ($formTipo === 'edit')
                                        Si desmarcás todos, se eliminarán los permisos asignados.
                                    @endif
                                </p>

                                @foreach ($permisosPorModulo as $modulo => $permisosModulo)
                                    <div class="card mb-2">
                                        <div class="card-header py-1">
                                            <strong>{{ $modulo }}</strong>
                                            <span class="badge bg-secondary ms-2">{{ $permisosModulo->count() }}</span>
                                        </div>
                                        <div class="card-body py-2">
                                            <div class="row">
                                                @foreach ($permisosModulo as $permiso)
                                                    <div class="col-md-6 mb-1">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox"
                                                                wire:model="formPermisosSeleccionados"
                                                                value="{{ $permiso->id }}"
                                                                id="permiso_{{ $permiso->id }}"
                                                                class="custom-control-input">
                                                            <label class="custom-control-label small"
                                                                for="permiso_{{ $permiso->id }}">
                                                                {{ ucfirst(str_replace('_', ' ', $permiso->name)) }}
                                                                @if ($permiso->description)
                                                                    <br><small class="text-muted">{{ $permiso->description }}</small>
                                                                @endif
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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
                        <button type="submit" form="form-rol" class="btn btn-ops-primary"
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
                        <p>¿Estás seguro de que deseas eliminar este rol?</p>
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
