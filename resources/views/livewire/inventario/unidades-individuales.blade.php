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
                            <td colspan="6" class="text-center text-muted py-4">
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

    {{-- Modal: alta de unidad --}}
    <div class="modal fade @if ($mostrarModalAlta) show d-block @endif" tabindex="-1"
         style="@if ($mostrarModalAlta) background: rgba(0,0,0,.5); @endif">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="darDeAlta">
                    <div class="modal-header">
                        <h5 class="modal-title">Nueva unidad</h5>
                        <button type="button" class="close" wire:click="cerrarModales">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Ítem</label>
                            <select wire:model="altaItemId" class="form-control @error('altaItemId') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->codigo }} — {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                            @error('altaItemId') <span class="invalid-feedback">{{ $message }}</span> @enderror
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
                            @error('altaUbicacionId') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Motivo (opcional)</label>
                            <input type="text" wire:model="altaMotivo" class="form-control" placeholder="ej: compra, donación...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModales">Cancelar</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Dar de alta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: asignar / transferir --}}
    <div class="modal fade @if ($mostrarModalAsignar) show d-block @endif" tabindex="-1"
         style="@if ($mostrarModalAsignar) background: rgba(0,0,0,.5); @endif">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="asignar">
                    <div class="modal-header">
                        <h5 class="modal-title">Asignar / transferir unidad</h5>
                        <button type="button" class="close" wire:click="cerrarModales">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nueva ubicación</label>
                            <select wire:model="asignarUbicacionId" class="form-control @error('asignarUbicacionId') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach ($ubicaciones as $ubicacion)
                                    <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                                @endforeach
                            </select>
                            @error('asignarUbicacionId') <span class="invalid-feedback">{{ $message }}</span> @enderror
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModales">Cancelar</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: dar de baja --}}
    <div class="modal fade @if ($mostrarModalBaja) show d-block @endif" tabindex="-1"
         style="@if ($mostrarModalBaja) background: rgba(0,0,0,.5); @endif">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="confirmarBaja">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Dar de baja unidad</h5>
                        <button type="button" class="close" wire:click="cerrarModales">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Esta acción es definitiva: la unidad queda fuera de servicio.</p>
                        <div class="form-group">
                            <label>Motivo</label>
                            <textarea wire:model="bajaMotivo" class="form-control @error('bajaMotivo') is-invalid @enderror" rows="3"></textarea>
                            @error('bajaMotivo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModales">Cancelar</button>
                        <button type="submit" class="btn btn-danger" wire:loading.attr="disabled">Dar de baja</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>