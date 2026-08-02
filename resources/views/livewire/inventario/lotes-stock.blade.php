<div>
    @if ($resumenReposicion->isNotEmpty())
        <div class="card-outline-ops mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-triangle-exclamation"></i> A reponer con el proveedor</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Ítem</th>
                            <th class="text-center">Vigente en depósito</th>
                            <th class="text-center">Vencido en depósito</th>
                            <th class="text-center">Mínimo configurado</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resumenReposicion as $r)
                            <tr>
                                <td>{{ $r['item']->codigo }} — {{ $r['item']->nombre }}</td>
                                <td class="text-center">{{ $r['vigente'] }}</td>
                                <td class="text-center">
                                    @if ($r['vencido'] > 0)
                                        <span class="badge-ops badge-ops-danger">{{ $r['vencido'] }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">{{ $r['item']->stock_minimo ?? '—' }}</td>
                                <td>
                                    @if ($r['bajoMinimo'])
                                        <span class="badge badge-warning">Bajo mínimo</span>
                                    @endif
                                    @if ($r['vencido'] > 0)
                                        <span class="badge-ops badge-ops-danger">Tiene stock vencido</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header p-0 pt-1">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'lotes' ? 'active' : '' }}" href="#" wire:click.prevent="$set('tab', 'lotes')">
                        Por cantidad (lotes)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'individuales' ? 'active' : '' }}" href="#" wire:click.prevent="$set('tab', 'individuales')">
                        Unidades individuales
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-3 pb-0">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <input type="text" wire:model.live.debounce.400ms="busqueda"
                           class="form-control" placeholder="Buscar por ítem, código{{ $tab === 'individuales' ? ' o N° de serie' : '' }}...">
                </div>
                <div class="col-md-4">
                    <select wire:model.live="filtroItemId" class="form-control">
                        <option value="">Todos los ítems</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->codigo }} — {{ $item->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filtroEstado" class="form-control">
                        <option value="">Todos</option>
                        @if ($tab === 'lotes')
                            <option value="con_stock">Con stock</option>
                        @endif
                        <option value="vencidos">Vencidos</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            @if ($tab === 'lotes')
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-ops">
                        <tr>
                            <th>Ítem</th>
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
                                    <span class="badge {{ $lote->cantidad_actual > 0 ? 'badge-success' : 'badge-light text-muted' }}">
                                        {{ $lote->cantidad_actual }}
                                    </span>
                                </td>
                                <td>{{ $lote->referencia ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No hay lotes en depósito que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-ops">
                        <tr>
                            <th>Ítem</th>
                            <th>N° de serie</th>
                            <th>Fecha recibido</th>
                            <th>Vencimiento</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($unidades as $unidad)
                            <tr wire:key="unidad-{{ $unidad->id }}" class="{{ $unidad->estaVencida() ? 'table-danger' : '' }}">
                                <td>{{ $unidad->item->codigo }} — {{ $unidad->item->nombre }}</td>
                                <td>{{ $unidad->numero_serie ?? "unidad #{$unidad->id}" }}</td>
                                <td>{{ $unidad->fecha_recibido?->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    @if ($unidad->vencimiento)
                                        {{ $unidad->vencimiento->format('d/m/Y') }}
                                        @if ($unidad->estaVencida())
                                            <span class="badge badge-danger ml-1">Vencido</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Sin vencimiento</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $unidad->estado === 'disponible' ? 'success' : 'warning' }}">
                                        {{ ucfirst($unidad->estado) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay unidades individuales en depósito que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card-footer">
            {{ $tab === 'lotes' ? $lotes->links() : $unidades->links() }}
        </div>
    </div>
</div>