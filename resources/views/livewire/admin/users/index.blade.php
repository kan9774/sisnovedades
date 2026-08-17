<div>
    <x-ops-card title="Usuarios" icon="user" eyebrow="{{ $usuarios->total() ?? 0 }} registros">
        <x-slot name="actions">
            @if ($viewMode === 'papelera')
                <button wire:click="verActivos" class="btn-ops btn-ops-secondary btn-sm me-1">
                    <i class="fas fa-arrow-left"></i> Activos
                </button>
            @else
                @can('viewTrashed', \App\Models\User::class)
                    <button wire:click="verPapelera" class="btn-ops btn-ops-warning btn-sm me-1">
                        <i class="fas fa-user-slash"></i> Papelera
                    </button>
                @endcan
            @endif

            @if ($viewMode === 'activos')
                @can('create', \App\Models\User::class)
                    <x-btn-ops variant="primary" icon="plus" href="{{ route('admin.users.create') }}">
                        Nuevo Usuario
                    </x-btn-ops>
                @endcan
            @endif
        </x-slot>

        {{-- INDICADOR DE VISTA --}}
        @if ($viewMode === 'papelera')
            <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Papelera:</strong> Mostrando usuarios eliminados. Puedes restaurarlos o eliminarlos permanentemente.
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
                        placeholder="@if ($viewMode === 'papelera') Buscar en usuarios eliminados... @else Buscar por nombre, apellido, email o C.I.... @endif">
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

        {{-- TABLA PRINCIPAL --}}
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-ops">
                    <tr>
                        @if ($viewMode === 'activos')
                            <th>Grado</th>
                        @endif
                        <th>Nombre</th>
                        <th>Email</th>
                        @if ($viewMode === 'activos')
                            <th>Roles</th>
                            <th>Estado</th>
                        @endif
                        @if ($viewMode === 'papelera')
                            <th>Rol</th>
                            <th>Eliminado</th>
                        @endif
                        <th class="text-center" style="width: 180px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            @if ($viewMode === 'activos')
                                <td>{{ $user->grade }}</td>
                            @endif
                            <td>{{ $user->name }} {{ $user->last_name }}</td>
                            <td>{{ $user->email }}</td>
                            @if ($viewMode === 'activos')
                                <td>
                                    @forelse($user->roles as $rol)
                                        <span class="badge-ops badge-ops-info mr-1 mb-1">
                                            {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                                        </span>
                                    @empty
                                        <span class="badge-ops badge-ops-secondary mb-1">Sin rol</span>
                                    @endforelse
                                    @if ($user->isSuperAdmin())
                                        <span class="badge-ops badge-ops-dark mb-1">SuperAdmin</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->status === \App\Enums\UserStatus::Active)
                                        <span class="badge-ops badge-ops-success">Activo</span>
                                    @else
                                        <span class="badge-ops badge-ops-secondary">Inactivo</span>
                                    @endif
                                </td>
                            @else
                                <td>
                                    <span class="badge-ops badge-ops-secondary">
                                        {{ $user->roles->first()->name ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $user->deleted_at->format('d/m/Y H:i') }}</td>
                            @endif
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    @if ($viewMode === 'activos')
                                        {{-- Editar --}}
                                        @can('update', $user)
                                            <a href="{{ route('admin.users.edit', $user->id) }}"
                                                class="btn-ops btn-ops-warning btn-xs mr-1" title="Editar usuario">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        {{-- Eliminar --}}
                                        @can('delete', $user)
                                            <button wire:click="confirmarEliminacion({{ $user->id }})"
                                                class="btn-ops btn-ops-danger btn-xs" title="Eliminar usuario">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    @else
                                        {{-- Papelera: Restaurar --}}
                                        @can('restore', $user)
                                            <button wire:click="restaurar({{ $user->id }})"
                                                class="btn-ops btn-ops-success btn-xs mr-1" title="Restaurar">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endcan

                                        {{-- Papelera: Eliminar permanentemente --}}
                                        @can('forceDelete', $user)
                                            <button wire:click="confirmarEliminacionPermanente({{ $user->id }})"
                                                class="btn-ops btn-ops-danger btn-xs" title="Eliminar permanentemente">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="@if ($viewMode === 'papelera') 6 @else 6 @endif" class="text-center text-muted py-4">
                                @if ($viewMode === 'papelera')
                                    <i class="fas fa-trash fa-2x d-block mb-2"></i>
                                    No hay usuarios en la papelera.
                                @else
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No hay usuarios registrados.
                                    @can('create', \App\Models\User::class)
                                        <br>
                                        <a href="{{ route('admin.users.create') }}" class="btn-ops btn-ops-primary btn-sm mt-2">
                                            <i class="fas fa-plus-circle"></i> Crear el primero
                                        </a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($usuarios->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $usuarios->links() }}
            </div>
        @endif
    </x-ops-card>

    {{-- SECCIÓN: USUARIOS INCOMPLETOS --}}
    @if ($viewMode === 'activos' && $usuariosIncompletos->isNotEmpty())
        <x-ops-card title="Usuarios incompletos" icon="exclamation-triangle"
            eyebrow="{{ $usuariosIncompletos->count() }} registros pendientes" class="mt-4">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-ops">
                        <tr>
                            <th>C.I.</th>
                            <th>Creado</th>
                            <th>Paso alcanzado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuariosIncompletos as $user)
                            <tr wire:key="incompleto-{{ $user->id }}">
                                <td>{{ $user->ci_formateado ?? $user->ci }}</td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @php
                                        $completoPaso2 = $user->grado_id && $user->paseVigente();
                                    @endphp
                                    <div class="mini-wizard">
                                        @foreach (['Cédula', 'Grado / Unidad', 'Datos Personales'] as $idx => $etiqueta)
                                            @php
                                                $stepNum = $idx + 1;
                                                $isDone = $completoPaso2 ? $stepNum <= 2 : $stepNum === 1;
                                                $isActive = !$isDone && $stepNum === ($completoPaso2 ? 3 : 2);
                                            @endphp
                                            <div
                                                class="mini-wizard__step {{ $isActive ? 'is-active' : '' }} {{ $isDone ? 'is-done' : '' }}">
                                                <div class="mini-wizard__circle">
                                                    @if ($isDone && $stepNum < 3)
                                                        <i class="fas fa-check"></i>
                                                    @elseif (!$isDone)
                                                        {{ $stepNum }}
                                                    @else
                                                        <i class="fas fa-lock"></i>
                                                    @endif
                                                </div>
                                                <div class="mini-wizard__label">{{ $etiqueta }}</div>
                                            </div>
                                            @if ($idx < 2)
                                                <div class="mini-wizard__line {{ $isDone ? 'is-done' : '' }}"></div>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center">
                                        <a href="{{ route('admin.users.create.resume', $user->id) }}"
                                            class="btn-ops btn-ops-info btn-xs mr-1" title="Retomar wizard">
                                            <i class="fas fa-play"></i> Retomar
                                        </a>
                                        <button wire:click="destroyIncompleto({{ $user->id }})"
                                            wire:confirm="Esto borra el registro por completo, incluido cualquier historial ya generado. ¿Continuar?"
                                            class="btn-ops btn-ops-danger btn-xs" title="Eliminar por completo">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ops-card>
    @endif

    {{-- MODAL: CONFIRMAR ELIMINACIÓN PERMANENTE (papelera) --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalForceDeleteUser" x-data x-init="$watch('$wire.usuarioAEliminarId', value => {
            if (value) document.body.classList.add('ops-panel-open');
            else document.body.classList.remove('ops-panel-open');
        })"
            :class="{ 'is-open': $wire.usuarioAEliminarId !== null }"
            wire:click.self="usuarioAEliminarId = null">
            <div class="ops-panel ops-panel--sm">
                <div class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">Peligro</span>
                            <h5 class="ops-panel__title">Eliminar Permanentemente</h5>
                        </div>
                        <button type="button" class="ops-panel__close" wire:click="usuarioAEliminarId = null" title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body">
                        <div class="ops-panel__content">
                            <div class="text-center mb-3">
                                <i class="fas fa-skull-crossbones fa-3x text-danger"></i>
                            </div>
                            <p class="text-center mb-0 fw-bold text-danger">
                                ¿Está seguro que desea eliminar permanentemente este usuario?
                            </p>
                            <p class="text-center mt-3">
                                Esta acción <strong>no se puede deshacer</strong>.
                            </p>
                            <p class="text-danger small text-center mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle"></i> Solo Super Administradores pueden realizar esta acción.
                            </p>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <button type="button" class="btn btn-outline-secondary"
                            wire:click="usuarioAEliminarId = null" wire:loading.attr="disabled">
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

    {{-- MODAL: CONFIRMAR ELIMINACIÓN PERMANENTE DE INCOMPLETO --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalForceDeleteIncompleto" x-data x-init="$watch('$wire.incompletoAEliminarId', value => {
            if (value) document.body.classList.add('ops-panel-open');
            else document.body.classList.remove('ops-panel-open');
        })"
            :class="{ 'is-open': $wire.incompletoAEliminarId !== null }"
            wire:click.self="incompletoAEliminarId = null">
            <div class="ops-panel ops-panel--sm">
                <div class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">Peligro</span>
                            <h5 class="ops-panel__title">Eliminar Registro Incompleto</h5>
                        </div>
                        <button type="button" class="ops-panel__close" wire:click="incompletoAEliminarId = null" title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body">
                        <div class="ops-panel__content">
                            <div class="text-center mb-3">
                                <i class="fas fa-skull-crossbones fa-3x text-danger"></i>
                            </div>
                            <p class="text-center mb-0 fw-bold text-danger">
                                ¿Está seguro que desea eliminar por completo este registro incompleto?
                            </p>
                            <p class="text-center mt-3">
                                Esta acción eliminará en cascada:
                            </p>
                            <ul class="text-muted small text-center mt-2 mb-0">
                                <li>Historial de grados</li>
                                <li>Historial de estados</li>
                                <li>Pases</li>
                                <li>Comisiones</li>
                            </ul>
                            <p class="text-danger small text-center mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.
                            </p>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <button type="button" class="btn btn-outline-secondary"
                            wire:click="incompletoAEliminarId = null" wire:loading.attr="disabled">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-ops-danger"
                            wire:click="ejecutarEliminacionPermanenteIncompleto"
                            wire:loading.attr="disabled">
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span>
                            @endif
                            <i class="fas fa-trash"></i> Eliminar por Completo
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
