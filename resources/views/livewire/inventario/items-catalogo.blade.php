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
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.400ms="busqueda"
                           class="form-control" placeholder="Buscar por código o nombre...">
                </div>
                <div class="col-md-4">
                    <select wire:model.live="filtroCategoriaId" class="form-control">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 text-right">
                    @can('create', \App\Models\Item::class)
                        <button wire:click="abrirModalCrear" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo ítem
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Talla</th>
                        <th>Seguimiento</th>
                        <th>Stock mín.</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="item-{{ $item->id }}">
                            <td>{{ $item->codigo }}</td>
                            <td>{{ $item->nombre }}</td>
                            <td>{{ $item->categoria->nombre }}</td>
                            <td>{{ $item->talla->valor ?? '—' }}</td>
                            <td>
                                @if ($item->tipo_seguimiento === 'individual')
                                    <span class="badge badge-info">Individual</span>
                                @else
                                    <span class="badge badge-secondary">
                                        Cantidad ({{ $item->unidad_medida }})
                                    </span>
                                @endif
                            </td>
                            <td>{{ $item->stock_minimo ?? '—' }}</td>
                            <td class="text-right">
                                @can('update', $item)
                                    <button wire:click="abrirModalEditar({{ $item->id }})"
                                            class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                @endcan
                                @can('delete', $item)
                                    <button wire:click="eliminar({{ $item->id }})"
                                            wire:confirm="¿Eliminar este ítem del catálogo?"
                                            class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay ítems que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $items->links() }}
        </div>
    </div>

    {{-- Modal de alta/edición --}}
    <div class="modal fade @if ($mostrarModal) show d-block @endif" tabindex="-1"
         style="@if ($mostrarModal) background: rgba(0,0,0,.5); @endif">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="guardar">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $itemId ? 'Editar ítem' : 'Nuevo ítem' }}
                        </h5>
                        <button type="button" class="close" wire:click="cerrarModal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Código</label>
                                <input type="text" wire:model="codigo" class="form-control @error('codigo') is-invalid @enderror">
                                @error('codigo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group col-md-8">
                                <label>Nombre</label>
                                <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror">
                                @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea wire:model="descripcion" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Categoría</label>
                                <select wire:model="categoria_id" class="form-control @error('categoria_id') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('categoria_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label>Talla (opcional)</label>
                                <select wire:model="talla_id" class="form-control">
                                    <option value="">No aplica</option>
                                    @foreach ($tallas as $talla)
                                        <option value="{{ $talla->id }}">
                                            {{ $talla->valor }} @if($talla->sistema) ({{ $talla->sistema }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Tipo de seguimiento</label>
                                <select wire:model.live="tipo_seguimiento" class="form-control">
                                    <option value="cantidad">Por cantidad</option>
                                    <option value="individual">Individual (con nº de serie)</option>
                                </select>
                            </div>

                            @if ($tipo_seguimiento === 'cantidad')
                                <div class="form-group col-md-4">
                                    <label>Unidad de medida</label>
                                    <input type="text" wire:model="unidad_medida" placeholder="unidad, caja, litro..."
                                           class="form-control @error('unidad_medida') is-invalid @enderror">
                                    @error('unidad_medida') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Stock mínimo</label>
                                    <input type="number" wire:model="stock_minimo" min="0" class="form-control">
                                </div>
                            @endif
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