<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <input type="text" wire:model.live.debounce.400ms="busqueda"
                           class="form-control" placeholder="Buscar por nombre...">
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- FILA DE ALTA --}}
            @can('create', \App\Models\Categoria::class)
                <form wire:submit="agregar">
                    <div class="row align-items-start mb-3">
                        <div class="col-md-9">
                            <label class="font-weight-bold">Nombre</label>
                            <input type="text" wire:model="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   placeholder="Ej: Uniformes, Herramientas...">
                            @error('nombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="font-weight-bold d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block"
                                    wire:loading.attr="disabled" wire:target="agregar">
                                <span wire:loading.remove wire:target="agregar"><i class="fas fa-plus"></i> Agregar</span>
                                <span wire:loading wire:target="agregar"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                            </button>
                        </div>
                    </div>
                </form>
            @endcan

            {{-- TABLA --}}
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-ops">
                        <tr>
                            <th>Nombre</th>
                            <th style="width: 12%" class="text-center">Ítems</th>
                            <th style="width: 12%" class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categorias as $categoria)
                            <tr wire:key="categoria-{{ $categoria->id }}">
                                @if ($editingId === $categoria->id)
                                    {{-- FILA EN MODO EDICIÓN --}}
                                    <td>
                                        <input type="text" wire:model="editNombre"
                                               class="form-control form-control-sm @error('editNombre') is-invalid @enderror">
                                        @error('editNombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $categoria->items_count }}</span>
                                    </td>
                                    <td class="text-right">
                                        <button wire:click="saveEdit" class="btn btn-success btn-sm" title="Guardar"
                                                wire:loading.attr="disabled" wire:target="saveEdit">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button wire:click="cancelEdit" class="btn btn-outline-secondary btn-sm" title="Cancelar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                @else
                                    {{-- FILA NORMAL --}}
                                    <td>{{ $categoria->nombre }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $categoria->items_count }}</span>
                                    </td>
                                    <td class="text-right">
                                        @can('update', $categoria)
                                            <button wire:click="startEdit({{ $categoria->id }})"
                                                    class="btn btn-outline-secondary btn-sm" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        @endcan
                                        @can('delete', $categoria)
                                            <button wire:click="eliminar({{ $categoria->id }})"
                                                    wire:confirm="¿Eliminar esta categoría?"
                                                    class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    No hay categorías que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $categorias->links() }}
        </div>
    </div>
</div>
