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
                <div class="col-md-4 text-right">
                    @can('create', \App\Models\Categoria::class)
                        <button wire:click="abrirModalCrear" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva categoría
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Ítems asociados</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categorias as $categoria)
                        <tr wire:key="categoria-{{ $categoria->id }}">
                            <td>{{ $categoria->nombre }}</td>
                            <td>{{ $categoria->descripcion ?? '—' }}</td>
                            <td>{{ $categoria->items_count }}</td>
                            <td class="text-right">
                                @can('update', $categoria)
                                    <button wire:click="abrirModalEditar({{ $categoria->id }})"
                                            class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                @endcan
                                @can('delete', $categoria)
                                    <button wire:click="eliminar({{ $categoria->id }})"
                                            wire:confirm="¿Eliminar esta categoría?"
                                            class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No hay categorías que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $categorias->links() }}
        </div>
    </div>

    {{-- Modal de alta/edición --}}
    <div class="modal fade @if ($mostrarModal) show d-block @endif" tabindex="-1"
         style="@if ($mostrarModal) background: rgba(0,0,0,.5); @endif">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="guardar">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $categoriaId ? 'Editar categoría' : 'Nueva categoría' }}
                        </h5>
                        <button type="button" class="close" wire:click="cerrarModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror">
                            @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Descripción (opcional)</label>
                            <textarea wire:model="descripcion" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="guardar" class="spinner-border spinner-border-sm"></span>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>