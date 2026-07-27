<div>
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.400ms="busqueda"
                           class="form-control" placeholder="Buscar por ítem o código...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filtroItemId" class="form-control">
                        <option value="">Todos los ítems</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->codigo }} — {{ $item->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filtroUbicacionId" class="form-control">
                        <option value="">Todas las ubicaciones</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filtroEstado" class="form-control">
                        <option value="">Todos</option>
                        <option value="con_stock">Con stock</option>
                        <option value="vencidos">Vencidos</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Ítem</th>
                        <th>Ubicación</th>
                        <th>Proveedor</th>
                        <th>Fecha recibido</th>
                        <th>Vencimiento</th>
                        <th class="text-center">Cantidad inicial</th>
                        <th class="text-center">Cantidad actual</th>
                        <th>Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lotes as $lote)
                        <tr wire:key="lote-{{ $lote->id }}" class="{{ $lote->vencido ? 'table-danger' : '' }}">
                            <td>{{ $lote->item->codigo }} — {{ $lote->item->nombre }}</td>
                            <td>{{ $lote->ubicacion->nombre }}</td>
                            <td>{{ $lote->proveedor->nombre ?? '—' }}</td>
                            <td>{{ $lote->fecha_recibido->format('d/m/Y') }}</td>
                            <td>
                                @if ($lote->vencimiento)
                                    {{ $lote->vencimiento->format('d/m/Y') }}
                                    @if ($lote->vencido)
                                        <span class="badge badge-danger ml-1">Vencido</span>
                                    @endif
                                @else
                                    <span class="text-muted">Sin vencimiento</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $lote->cantidad_inicial }}</td>
                            <td class="text-center">
                                <span class="badge {{ $lote->cantidad_actual > 0 ? 'badge-secondary' : 'badge-light text-muted' }}">
                                    {{ $lote->cantidad_actual }}
                                </span>
                            </td>
                            <td>{{ $lote->referencia ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No hay lotes que coincidan con los filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $lotes->links() }}
        </div>
    </div>
</div>