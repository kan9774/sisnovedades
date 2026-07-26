<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        {{-- Formulario de registro --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Registrar movimiento</h3>
                </div>
                <form wire:submit="registrar">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tipo de movimiento</label>
                            <select wire:model.live="tipo" class="form-control">
                                @can('registrarEntrada', \App\Models\Movimiento::class)
                                    <option value="entrada">Entrada</option>
                                @endcan
                                @can('registrarSalida', \App\Models\Movimiento::class)
                                    <option value="salida">Salida</option>
                                @endcan
                                @can('registrarTransferencia', \App\Models\Movimiento::class)
                                    <option value="transferencia">Transferencia</option>
                                @endcan
                                @can('registrarAjuste', \App\Models\Movimiento::class)
                                    <option value="ajuste">Ajuste (conteo físico)</option>
                                @endcan
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Ítem</label>
                            <select wire:model.live="item_id" class="form-control @error('item_id') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->codigo }} — {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                            @error('item_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        @if (in_array($tipo, ['salida', 'transferencia']))
                            <div class="form-group">
                                <label>Ubicación de origen</label>
                                <select wire:model.live="ubicacion_origen_id"
                                        class="form-control @error('ubicacion_origen_id') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('ubicacion_origen_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if (in_array($tipo, ['entrada', 'transferencia', 'ajuste']))
                            <div class="form-group">
                                <label>Ubicación de destino</label>
                                <select wire:model.live="ubicacion_destino_id"
                                        class="form-control @error('ubicacion_destino_id') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('ubicacion_destino_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if ($this->stockDeReferencia !== null)
                            <div class="alert alert-light border small py-1 px-2">
                                Stock actual en esa ubicación: <strong>{{ $this->stockDeReferencia }}</strong>
                            </div>
                        @endif

                        <div class="form-group">
                            <label>{{ $tipo === 'ajuste' ? 'Cantidad real contada' : 'Cantidad' }}</label>
                            <input type="number" wire:model="cantidad" min="0"
                                   class="form-control @error('cantidad') is-invalid @enderror">
                            @error('cantidad') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Motivo (opcional)</label>
                            <input type="text" wire:model="motivo" class="form-control">
                        </div>

                        @if ($tipo !== 'ajuste')
                            <div class="form-group">
                                <label>Referencia (opcional)</label>
                                <input type="text" wire:model="referencia" class="form-control"
                                       placeholder="ej: nº de novedad, orden de compra...">
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled">
                            <span wire:loading wire:target="registrar" class="spinner-border spinner-border-sm"></span>
                            Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Historial --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="card-title">Historial de movimientos</h3>
                        </div>
                        <div class="col-md-3">
                            <select wire:model.live="filtroTipo" class="form-control form-control-sm">
                                <option value="">Todos los tipos</option>
                                <option value="entrada">Entrada</option>
                                <option value="salida">Salida</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="ajuste">Ajuste</option>
                                <option value="baja">Baja</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select wire:model.live="filtroItemId" class="form-control form-control-sm">
                                <option value="">Todos los ítems</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->codigo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Ítem</th>
                                <th>Cantidad</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movimientos as $movimiento)
                                <tr wire:key="mov-{{ $movimiento->id }}">
                                    <td>{{ $movimiento->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge
                                            @switch($movimiento->tipo)
                                                @case('entrada') badge-success @break
                                                @case('salida') badge-danger @break
                                                @case('transferencia') badge-info @break
                                                @case('ajuste') badge-warning @break
                                                @default badge-secondary
                                            @endswitch
                                        ">
                                            {{ ucfirst($movimiento->tipo) }}
                                        </span>
                                    </td>
                                    <td>{{ $movimiento->item->nombre }}</td>
                                    <td>{{ $movimiento->cantidad ?? '—' }}</td>
                                    <td>{{ $movimiento->ubicacionOrigen->nombre ?? '—' }}</td>
                                    <td>{{ $movimiento->ubicacionDestino->nombre ?? '—' }}</td>
                                    <td>{{ $movimiento->usuario->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No hay movimientos registrados todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $movimientos->links() }}
                </div>
            </div>
        </div>
    </div>
</div>