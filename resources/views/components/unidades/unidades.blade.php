<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-outline-ops">
        <div class="card-header-ops">
            <div class="card-header-ops__title-wrap">
                <h3 class="card-title-ops mb-0">
                    <i class="fas fa-building"></i> Unidades
                </h3>
            </div>
            <div class="card-tools">
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       class="form-control form-control-sm"
                       placeholder="Buscar por nombre..."
                       style="width: 200px;">
            </div>
        </div>

        <div class="card-body">
            @can('create', App\Models\Unidad::class)
                <form wire:submit="guardar" class="mb-3">
                    <div class="form-row align-items-end">
                        <div class="col-md-7">
                            <label class="small mb-1">Nombre</label>
                            <input type="text"
                                   wire:model="nombre"
                                   class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                                   placeholder="Ej: Compañía, Batallón, Regimiento">
                        </div>
                        <div class="col-md-2">
                            <label class="small mb-1 d-block">&nbsp;</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="unidad-activo"
                                       wire:model="activo">
                                <label class="custom-control-label" for="unidad-activo">Activo</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex" style="gap: .5rem;">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill"
                                        wire:loading.attr="disabled" wire:target="guardar">
                                    <span wire:loading.remove wire:target="guardar">
                                        <i class="fas {{ $editingId ? 'fa-save' : 'fa-plus' }}"></i>
                                        {{ $editingId ? 'Actualizar' : 'Agregar' }}
                                    </span>
                                    <span wire:loading wire:target="guardar">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </button>
                                @if ($editingId)
                                    <button type="button" wire:click="cancelar" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @error('nombre') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </form>
            @endcan
        </div>

        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-ops">
                    <tr>
                        <th>Nombre</th>
                        <th>Vehículos</th>
                        <th>Usuarios</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unidades as $unidad)
                        <tr wire:key="unidad-{{ $unidad->id }}"
                            @class(['table-active' => $editingId === $unidad->id])>
                            <td>{{ $unidad->nombre }}</td>
                            <td>{{ $unidad->vehiculos_count }}</td>
                            <td>{{ $unidad->usuarios_count }}</td>
                            <td>
                                @if ($unidad->activo)
                                    <span class="badge-ops badge-ops-success">Activo</span>
                                @else
                                    <span class="badge-ops badge-ops-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                @php $tieneRelaciones = $unidad->vehiculos_count > 0 || $unidad->usuarios_count > 0; @endphp
                                <div class="d-flex justify-content-center">
                                    @can('update', $unidad)
                                        <button type="button"
                                                wire:click="editar({{ $unidad->id }})"
                                                class="btn-ops btn-ops-warning btn-xs mr-1">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    @can('delete', $unidad)
                                        @if ($tieneRelaciones)
                                            <button type="button"
                                                    class="btn-ops btn-ops-danger btn-xs"
                                                    style="opacity: .4;"
                                                    disabled
                                                    title="No se puede eliminar: tiene vehículos o usuarios asignados">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                    wire:click="eliminar({{ $unidad->id }})"
                                                    wire:confirm="¿Eliminar esta unidad?"
                                                    class="btn-ops btn-ops-danger btn-xs">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-building fa-2x d-block mb-2"></i>
                                No hay unidades registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($unidades->hasPages())
            <div class="card-footer">
                {{ $unidades->links() }}
            </div>
        @endif
    </div>
</div>