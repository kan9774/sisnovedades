<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <input type="text" wire:model.live.debounce.400ms="busqueda"
                           class="form-control" placeholder="Buscar por nº de serie...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filtroItemId" class="form-control">
                        <option value="">Todos los ítems</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filtroEstado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="disponible">Disponible</option>
                        <option value="asignado">Asignado</option>
                        <option value="en_reparacion">En reparación</option>
                        <option value="baja">Dado de baja</option>
                    </select>
                </div>
                <div class="col-md-3 text-right">
                    @can('create', \App\Models\ItemUnidad::class)
                        <button wire:click="abrirModalAlta" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva unidad
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nº de serie</th>
                        <th>Ítem</th>
                        <th>Estado</th>
                        <th>Vencimiento</th>
                        <th>Ubicación actual</th>
                        <th>Responsable</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unidades as $unidad)
                        <tr wire:key="unidad-{{ $unidad->id }}">
                            <td>{{ $unidad->numero_serie ?? '—' }}</td>
                            <td>{{ $unidad->item->nombre }}</td>
                            <td>
                                <span class="badge
                                    @switch($unidad->estado)
                                        @case('disponible') badge-success @break
                                        @case('asignado') badge-info @break
                                        @case('en_reparacion') badge-warning @break
                                        @case('baja') badge-secondary @break
                                    @endswitch
                                ">
                                    {{ str_replace('_', ' ', ucfirst($unidad->estado)) }}
                                </span>
                            </td>
                            <td>
                                @if ($unidad->vencimiento)
                                    @if ($unidad->estaVencida())
                                        <span class="badge badge-danger" title="Venció el {{ $unidad->vencimiento->format('d/m/Y') }}">
                                            <i class="fas fa-exclamation-triangle"></i> Vencida
                                        </span>
                                    @else
                                        <span class="text-muted small">{{ $unidad->vencimiento->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $unidad->ubicacionActual->nombre ?? '—' }}</td>
                            <td>{{ $unidad->responsable->name ?? '—' }}</td>
                            <td class="text-right">
                                @if ($unidad->estado !== 'baja')
                                    @can('asignar', $unidad)
                                        <button wire:click="abrirModalAsignar({{ $unidad->id }})"
                                                class="btn btn-sm btn-outline-primary" title="Asignar / transferir">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    @endcan
                                    @can('marcarEnReparacion', $unidad)
                                        @if ($unidad->estado !== 'en_reparacion')
                                            <button wire:click="marcarEnReparacion({{ $unidad->id }})"
                                                    wire:confirm="¿Marcar esta unidad como en reparación?"
                                                    class="btn btn-sm btn-outline-warning" title="Enviar a reparación">
                                                <i class="fas fa-tools"></i>
                                            </button>
                                        @endif
                                    @endcan
                                    @can('darDeBaja', $unidad)
                                        <button wire:click="abrirModalBaja({{ $unidad->id }})"
                                                class="btn btn-sm btn-outline-danger" title="Dar de baja">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                @else
                                    <span class="text-muted small">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay unidades registradas todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $unidades->links() }}
        </div>
    </div>

    {{-- Panel: alta de unidad (estilo ops) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalUnidadAlta" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="darDeAlta" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Inventario</span>
                        <h5 class="ops-panel__title">Nueva unidad</h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalUnidadAlta')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        <div class="form-group">
                            <label>Ítem</label>
                            <select wire:model.live="altaItemId" class="form-control @error('altaItemId') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->codigo }} — {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                            @error('altaItemId') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Número de serie (opcional)</label>
                            <input type="text" wire:model="altaNumeroSerie" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Ubicación inicial</label>
                            <select wire:model="altaUbicacionId" class="form-control @error('altaUbicacionId') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach ($ubicaciones as $ubicacion)
                                    <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                                @endforeach
                            </select>
                            @error('altaUbicacionId') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Proveedor (opcional)</label>
                                <select wire:model="altaProveedorId" class="form-control @error('altaProveedorId') is-invalid @enderror">
                                    <option value="">Sin especificar</option>
                                    @foreach ($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('altaProveedorId') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label>Fecha de recibido (opcional)</label>
                                <input type="date" wire:model="altaFechaRecibido"
                                       class="form-control @error('altaFechaRecibido') is-invalid @enderror">
                                @error('altaFechaRecibido') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                @if ($this->itemSeleccionadoTieneVidaUtil())
                                    <small class="form-text text-muted">
                                        Este ítem vence a los {{ $this->vidaUtilDelItemSeleccionado() }} meses de recibido.
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Motivo (opcional)</label>
                            <input type="text" wire:model="altaMotivo" class="form-control" placeholder="ej: compra, donación...">
                        </div>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalUnidadAlta')">Cancelar</button>
                    <button type="submit" class="btn btn-ops-primary" wire:loading.attr="disabled" wire:target="darDeAlta">
                        <span wire:loading.remove wire:target="darDeAlta"><i class="fas fa-save"></i> Dar de alta</span>
                        <span wire:loading wire:target="darDeAlta"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- Panel: asignar / transferir (estilo ops) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalUnidadAsignar" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="asignar" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Inventario</span>
                        <h5 class="ops-panel__title">Asignar / transferir unidad</h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalUnidadAsignar')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        <div class="form-group">
                            <label>Nueva ubicación</label>
                            <select wire:model="asignarUbicacionId" class="form-control @error('asignarUbicacionId') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach ($ubicaciones as $ubicacion)
                                    <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                                @endforeach
                            </select>
                            @error('asignarUbicacionId') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Responsable (opcional)</label>
                            <select wire:model="asignarResponsableId" class="form-control">
                                <option value="">Sin responsable puntual</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Motivo (opcional)</label>
                            <input type="text" wire:model="asignarMotivo" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalUnidadAsignar')">Cancelar</button>
                    <button type="submit" class="btn btn-ops-primary" wire:loading.attr="disabled" wire:target="asignar">
                        <span wire:loading.remove wire:target="asignar"><i class="fas fa-save"></i> Asignar</span>
                        <span wire:loading wire:target="asignar"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- Panel: dar de baja (estilo ops) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalUnidadBaja" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="confirmarBaja" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Inventario</span>
                        <h5 class="ops-panel__title">Dar de baja unidad</h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalUnidadBaja')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        <p class="text-muted">Esta acción es definitiva: la unidad queda fuera de servicio.</p>
                        <div class="form-group">
                            <label>Motivo</label>
                            <textarea wire:model="bajaMotivo" class="form-control @error('bajaMotivo') is-invalid @enderror" rows="3"></textarea>
                            @error('bajaMotivo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalUnidadBaja')">Cancelar</button>
                    <button type="submit" class="btn btn-danger" wire:loading.attr="disabled" wire:target="confirmarBaja">
                        <span wire:loading.remove wire:target="confirmarBaja"><i class="fas fa-trash"></i> Dar de baja</span>
                        <span wire:loading wire:target="confirmarBaja"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
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

        const opsPanelesUnidad = {
            'abrir-modal-unidad-alta': 'modalUnidadAlta',
            'abrir-modal-unidad-asignar': 'modalUnidadAsignar',
            'abrir-modal-unidad-baja': 'modalUnidadBaja',
        };

        Object.entries(opsPanelesUnidad).forEach(([evento, id]) => {
            $wire.on(evento, () => {
                document.getElementById(id).classList.add('is-open');
                document.body.classList.add('ops-panel-open');
            });
        });

        $wire.on('cerrar-modal-unidad', () => {
            cerrarOpsPanel('modalUnidadAlta');
            cerrarOpsPanel('modalUnidadAsignar');
            cerrarOpsPanel('modalUnidadBaja');
        });
    </script>
@endscript