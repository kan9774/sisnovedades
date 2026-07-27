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
                        <th>Vida útil</th>
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
                            <td>
                                @if ($item->vida_util_meses)
                                    {{ $item->vida_util_meses }} {{ Str::plural('mes', $item->vida_util_meses) }}
                                @else
                                    <span class="text-muted">No vence</span>
                                @endif
                            </td>
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
                            <td colspan="8" class="text-center text-muted py-4">
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

    {{-- Panel de alta/edición estilo ops --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalItem" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="guardar" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Inventario</span>
                        <h5 class="ops-panel__title">
                            {{ $itemId ? 'Editar ítem' : 'Nuevo ítem' }}
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalItem')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
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

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Vida útil (meses, opcional)</label>
                                <input type="number" wire:model="vida_util_meses" min="1"
                                       placeholder="Ej: 24"
                                       class="form-control @error('vida_util_meses') is-invalid @enderror">
                                @error('vida_util_meses') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                <small class="form-text text-muted">
                                    Dejar vacío si el ítem no vence (ej: mobiliario). Si tiene valor, sus
                                    unidades/lotes vencen a partir de la fecha en que se reciben del proveedor.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalItem')">Cancelar</button>
                    <button type="submit" class="btn btn-ops-primary" wire:loading.attr="disabled" wire:target="guardar">
                        <span wire:loading.remove wire:target="guardar"><i class="fas fa-save"></i> Guardar</span>
                        <span wire:loading wire:target="guardar"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>
</div>


@script
    <script>
        if (!window.cerrarOpsPanel) {
            window.cerrarOpsPanel = function (id) {
                const overlay = document.getElementById(id);
                if (overlay) overlay.classList.remove('is-open');
                document.body.classList.remove('ops-panel-open');
            };
        }

        $wire.on('abrir-modal-item', () => {
            document.getElementById('modalItem').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        $wire.on('cerrar-modal-item', () => {
            cerrarOpsPanel('modalItem');
        });
    </script>
@endscript