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

    <x-ops-card title="Tipos de Vehículo" icon="car" eyebrow="{{ $tiposVehiculo->total() }} registros">
        <x-slot name="actions">
            @can('create', \App\Models\TipoVehiculo::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                    Nuevo Tipo
                </x-btn-ops>
            @endcan
        </x-slot>

        {{-- BARRA DE BÚSQUEDA --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Buscar por nombre...">
            </div>
        </div>

        {{-- FORMULARIO INLINE (create / edit) --}}
        @if ($showForm)
            <div class="card mb-3" style="background-color: #f8f9fa;">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        @if ($formTipo === 'create')
                            <i class="fas fa-plus-circle text-primary"></i> Nuevo Tipo de Vehículo
                        @else
                            <i class="fas fa-edit text-warning"></i> Editar Tipo de Vehículo
                        @endif
                    </h6>

                    <form wire:submit="guardar">
                        <div class="row align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Nombre</label>
                                <input type="text" wire:model="formNombre"
                                    class="form-control @error('formNombre') is-invalid @enderror"
                                    placeholder="Ej: Camioneta, Auto, Camión">
                                @error('formNombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input type="checkbox" wire:model="formActivo" class="form-check-input"
                                        id="formActivo">
                                    <label class="form-check-label" for="formActivo">Activo</label>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="submit" class="btn btn-primary btn-sm me-2"
                                    wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                                    @if ($loading)
                                        <span class="spinner-border spinner-border-sm"></span>
                                    @endif
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <x-btn-ops variant="secondary" icon="times" wire:click="cerrarForm" size="sm">
                                    Cancelar
                                </x-btn-ops>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th style="width: 45%">Nombre</th>
                        <th style="width: 12%">Estado</th>
                        <th style="width: 18%">Vehículos</th>
                        <th style="width: 25%" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tiposVehiculo as $tipo)
                        <tr wire:key="tipo-{{ $tipo->id }}">
                            <td>{{ $tipo->nombre }}</td>
                            <td class="text-center">
                                @if ($tipo->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $tipo->vehiculos_count }}</span>
                            </td>
                            <td class="text-end">
                                @can('update', $tipo)
                                    <x-btn-ops variant="warning" icon="pen"
                                        wire:click="abrirEditar({{ $tipo->id }})"
                                        size="xs" title="Editar">
                                    </x-btn-ops>
                                @endcan
                                @can('delete', $tipo)
                                    <x-btn-ops variant="danger" icon="trash"
                                        wire:click="eliminar({{ $tipo->id }})"
                                        wire:confirm="¿Eliminar este tipo de vehículo?"
                                        size="xs" title="Eliminar">
                                    </x-btn-ops>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No hay tipos de vehículo cargados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($tiposVehiculo->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $tiposVehiculo->links() }}
            </div>
        @endif
    </x-ops-card>
</div>
