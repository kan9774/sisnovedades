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

    <x-ops-card eyebrow="BCOM1 · Inventario" icon="boxes" title="Catálogo de ítems">
        <x-slot:actions>
            @can('create', \App\Models\Item::class)
                <x-btn-ops variant="secondary" icon="file-excel" wire:click="abrirModalImportar">
                    Importar Excel
                </x-btn-ops>
                <x-btn-ops variant="primary" icon="plus" wire:click="abrirModalCrear">
                    Nuevo ítem
                </x-btn-ops>
            @endcan
        </x-slot:actions>
        <x-slot:header>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.400ms="busqueda" class="form-control"
                        placeholder="Buscar por código o nombre...">
                </div>
                <div class="col-md-6">
                    <select wire:model.live="filtroCategoriaId" class="form-control">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-slot:header>

        <div class="table-responsive">
            <table class="table table-hover table-ops-hover mb-0">
                <thead class="thead-ops">
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
                                    <span class="badge-ops badge-ops-info">Individual</span>
                                @else
                                    <span class="badge-ops badge-ops-secondary">
                                        Cantidad ({{ $item->unidad_medida }})
                                    </span>
                                @endif
                            </td>
                            <td>{{ $item->stock_minimo ?? '—' }}</td>
                            <td>
                                @if ($item->vida_util_meses)
                                    {{ $item->vida_util_meses }} {{ $item->vida_util_meses == 1 ? 'mes' : 'meses' }}
                                @else
                                    <span class="text-muted">No vence</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <x-ops-actions :model="$item" :edit="'abrirModalEditar(' . $item->id . ')'" :delete="'eliminar(' . $item->id . ')'" size="sm"
                                    deleteTitle="¿Eliminar este ítem del catálogo?"
                                    deleteText="Esta acción no se puede deshacer." />
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

        <x-slot:footer>
            {{ $items->links() }}
        </x-slot:footer>
    </x-ops-card>

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
                        <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalItem')"
                            title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body">
                        <div class="ops-panel__content">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Categoría</label>
                                    <select wire:model.live="categoria_id"
                                        class="form-control @error('categoria_id') is-invalid @enderror">
                                        <option value="">Seleccionar...</option>
                                        @foreach ($categorias as $categoria)
                                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('categoria_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Talla (opcional)</label>
                                    <select wire:model="talla_id" class="form-control">
                                        <option value="">No aplica</option>
                                        @foreach ($tallas as $talla)
                                            <option value="{{ $talla->id }}">
                                                {{ $talla->valor }} @if ($talla->sistema)
                                                    ({{ $talla->sistema }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>
                                        Código
                                        @if ($codigoAuto && $categoria_id)
                                            <small class="text-muted font-weight-normal">(sugerido)</small>
                                        @endif
                                    </label>
                                    <input type="text" wire:model.live.debounce.400ms="codigo"
                                        style="text-transform: uppercase"
                                        class="form-control @error('codigo') is-invalid @enderror">
                                    @error('codigo')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-8">
                                    <label>Nombre</label>
                                    <input type="text" wire:model="nombre" style="text-transform: uppercase"
                                        class="form-control @error('nombre') is-invalid @enderror">
                                    @error('nombre')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            @if ($categoria_id && $codigoAuto && $codigo === '')
                                <small class="text-warning d-block mb-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Esta categoría no tiene abreviatura configurada, cargá el código a mano
                                    (o agregale una abreviatura desde Inventario &gt; Categorías).
                                </small>
                            @endif

                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea wire:model="descripcion" class="form-control" rows="2"></textarea>
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
                                        <input type="text" wire:model="unidad_medida"
                                            style="text-transform: uppercase" placeholder="unidad, caja, litro..."
                                            class="form-control @error('unidad_medida') is-invalid @enderror">
                                        @error('unidad_medida')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Stock mínimo</label>
                                        <input type="number" wire:model="stock_minimo" min="0"
                                            class="form-control">
                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>Vida útil (meses, opcional)</label>
                                    <input type="number" wire:model="vida_util_meses" min="1"
                                        placeholder="Ej: 24"
                                        class="form-control @error('vida_util_meses') is-invalid @enderror">
                                    @error('vida_util_meses')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Dejar vacío si el ítem no vence (ej: mobiliario). Si tiene valor, sus
                                        unidades/lotes vencen a partir de la fecha en que se reciben del proveedor.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <button type="button" class="footer-btn btn-ops-secondary"
                            onclick="cerrarOpsPanel('modalItem')">Cancelar</button>
                        <x-btn-ops type="submit" variant="primary" wire:loading.attr="disabled"
                            wire:target="guardar">
                            <span wire:loading.remove wire:target="guardar"><i class="fas fa-save"></i> Guardar</span>
                            <span wire:loading wire:target="guardar"><i class="fas fa-spinner fa-spin"></i>
                                Guardando...</span>
                        </x-btn-ops>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalImportarItems" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="importar" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Inventario</span>
                        <h5 class="ops-panel__title">Importar ítems desde Excel</h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalImportarItems')"
                        title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">

                        <a href="{{ route('admin.inventario.items.plantilla') }}"
                            class="d-inline-flex align-items-center mb-3" download>
                            <i class="fas fa-download mr-1"></i> Descargar plantilla Excel
                        </a>

                        <div class="form-group">
                            <label>Archivo Excel</label>
                            <input type="file" wire:model="archivoExcel" accept=".xlsx,.xls"
                                class="form-control @error('archivoExcel') is-invalid @enderror">
                            @error('archivoExcel')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <div wire:loading wire:target="archivoExcel" class="text-muted small mt-1">
                                Cargando archivo...
                            </div>
                        </div>

                        @if (!empty($erroresImportacion))
                            <div class="alert alert-warning">
                                <strong>Se encontraron errores:</strong>
                                <ul class="mb-0">
                                    @foreach ($erroresImportacion as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="footer-btn btn-ops-secondary"
                        onclick="cerrarOpsPanel('modalImportarItems')">Cancelar</button>
                    <x-btn-ops type="submit" variant="primary" wire:loading.attr="disabled" wire:target="importar">
                        <span wire:loading.remove wire:target="importar">
                            <i class="fas fa-file-import"></i> Importar
                        </span>
                        <span wire:loading wire:target="importar">
                            <i class="fas fa-spinner fa-spin"></i> Importando...
                        </span>
                    </x-btn-ops>
                </div>
            </form>
        </div>
    </div>
</template>
</div>

@script
    <script>
        if (!window.cerrarOpsPanel) {
            window.cerrarOpsPanel = function(id) {
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

        // Nuevo: modal de importación
        $wire.on('abrir-modal-importar', () => {
            document.getElementById('modalImportarItems').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        $wire.on('cerrar-modal-importar', () => {
            cerrarOpsPanel('modalImportarItems');
        });
    </script>
@endscript