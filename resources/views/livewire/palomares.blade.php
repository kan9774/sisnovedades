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

    <x-ops-card title="Palomares" icon="dove" eyebrow="{{ $palomares->total() }} registros">
        <x-slot name="actions">
            @can('create', \App\Models\Palomar::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                    Nuevo Palomar
                </x-btn-ops>
            @endcan
        </x-slot>

        {{-- BARRA DE BÚSQUEDA --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Buscar por nombre o ubicación...">
            </div>
        </div>

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-hover table-ops-hover mb-0">
                <thead class="thead-ops">
                    <tr>
                        <th style="width: 50px">#</th>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Capacidad</th>
                        <th>Palomas</th>
                        <th style="width: 90px">Estado</th>
                        <th class="text-center" style="width: 140px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($palomares as $palomar)
                        <tr wire:key="palomar-{{ $palomar->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-bold">{{ $palomar->nombre }}</td>
                            <td>{{ $palomar->ubicacion ?? '—' }}</td>
                            <td>{{ $palomar->capacidad_maxima ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $palomar->palomas_count }}</span>
                            </td>
                            <td class="text-center align-middle">
                                @if ($palomar->activo)
                                    <span class="badge-ops badge-ops-success">Activo</span>
                                @else
                                    <span class="badge-ops badge-ops-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <div class="ops-actions justify-content-center">
                                    @can('update', $palomar)
                                        <x-btn-ops variant="warning" icon="pen"
                                            wire:click="abrirEditar({{ $palomar->id }})"
                                            size="xs" title="Editar">
                                        </x-btn-ops>
                                    @endcan
                                    @can('delete', $palomar)
                                        <x-btn-ops variant="danger" icon="trash"
                                            wire:click="confirmarEliminacion({{ $palomar->id }})"
                                            size="xs" title="Eliminar">
                                        </x-btn-ops>
                                    @endcan
                                    <a href="{{ route('admin.palomares.reporte', $palomar) }}"
                                        class="btn-ops btn-ops-danger btn-xs"
                                        title="Generar reporte PDF"
                                        aria-label="Generar reporte PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-home fa-2x d-block mb-2" style="opacity: 0.3;"></i>
                                No hay palomares registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($palomares->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $palomares->links() }}
            </div>
        @endif
    </x-ops-card>

    {{-- MODAL: FORMULARIO CREAR / EDITAR (ops-panel overlay) --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalPalomar" x-data x-init="$watch('$wire.showForm', value => {
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
                                    Nuevo Palomar
                                @else
                                    Editar Palomar
                                @endif
                            </h5>
                        </div>
                        <button type="button" class="ops-panel__close" wire:click="cerrarForm" title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body" wire:loading.class="opacity-50" wire:target="guardar">
                        <div class="ops-panel__content">
                            <form wire:submit="guardar" id="form-palomar">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nombre <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="formNombre"
                                                class="form-control form-control-sm @error('formNombre') is-invalid @enderror"
                                                placeholder="Ej: Palomar Principal">
                                            @error('formNombre')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Ubicación</label>
                                            <input type="text" wire:model="formUbicacion"
                                                class="form-control form-control-sm @error('formUbicacion') is-invalid @enderror"
                                                placeholder="Ej: Cuartel B, Sector Norte">
                                            @error('formUbicacion')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Capacidad máxima</label>
                                            <input type="number" wire:model="formCapacidadMaxima"
                                                min="0"
                                                class="form-control form-control-sm @error('formCapacidadMaxima') is-invalid @enderror"
                                                placeholder="Ej: 500">
                                            @error('formCapacidadMaxima')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group pt-4">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="formActivo" wire:model="formActivo">
                                                <label class="custom-control-label" for="formActivo">Activo</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Observaciones</label>
                                    <textarea wire:model="formObservaciones" rows="3"
                                        class="form-control form-control-sm @error('formObservaciones') is-invalid @enderror"
                                        placeholder="Observaciones adicionales..."></textarea>
                                    @error('formObservaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cerrarForm"
                            wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                            Cancelar
                        </button>
                        <button type="submit" form="form-palomar" class="btn btn-ops-primary"
                            wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span>
                            @endif
                            <i class="fas fa-save"></i>
                            {{ $formTipo === 'create' ? 'Crear Palomar' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
