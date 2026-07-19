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

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-building"></i> Unidades
            </h3>
            <div class="card-tools">
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       class="form-control form-control-sm"
                       placeholder="Buscar por nombre..."
                       style="width: 200px;">
            </div>
        </div>

        <div class="card-body">
            <form wire:submit="guardar" class="mb-3">
                <div class="form-row align-items-end">
                    <div class="col-md-8">
                        <label class="small mb-1">Nombre</label>
                        <input type="text"
                               wire:model="name"
                               class="form-control form-control-sm @error('name') is-invalid @enderror"
                               placeholder="Ej: J.Bn. Libertad o Muerte Com. Nº1">
                    </div>
                    <div class="col-md-4">
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
                @error('name') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
            </form>
        </div>

        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Novedades</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($organismos as $organismo)
                        <tr wire:key="organismo-{{ $organismo->id }}"
                            @class(['table-active' => $editingId === $organismo->id])>
                            <td>{{ $organismo->name }}</td>
                            <td>{{ $organismo->novedades_count }}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    <button type="button"
                                            wire:click="editar({{ $organismo->id }})"
                                            class="btn btn-outline-warning btn-xs mr-1"
                                            style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    @if ($organismo->novedades_count > 0)
                                        <button type="button"
                                                class="btn btn-outline-danger btn-xs"
                                                style="opacity: .4;"
                                                disabled
                                                title="No se puede eliminar: tiene novedades asociadas">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                                wire:click="eliminar({{ $organismo->id }})"
                                                wire:confirm="¿Eliminar este organismo?"
                                                class="btn btn-outline-danger btn-xs"
                                                style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                No hay organismos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($organismos->hasPages())
            <div class="card-footer">
                {{ $organismos->links() }}
            </div>
        @endif
    </div>
</div>