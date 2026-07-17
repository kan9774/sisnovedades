<div wire:poll.5s class="mt-4">
    @if ($this->salidasPendientes->isNotEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>{{ $this->salidasPendientes->total() }}</strong> salida(s) pendiente(s) de guardias anteriores
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Fecha Guardia</th>
                        <th>Vehículo</th>
                        <th>Conductor</th>
                        <th>Hora Sale</th>
                        <th>Combustible</th>
                        <th>Km Sale</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->salidasPendientes as $index => $salida)
                        <tr wire:key="salida-{{ $salida->id }}">
                            <td>{{ $index + 1 + ($this->salidasPendientes->currentPage() - 1) * $this->salidasPendientes->perPage() }}</td>
                            <td>{{ $salida->guardia->date->format('d/m/Y') }}</td>
                            <td>{{ $salida->vehiculo->matricula }} - {{ $salida->vehiculo->tipo . ' ' . $salida->vehiculo->marca }}</td>
                            <td>{{ $salida->conductor->primer_apellido }}, {{ $salida->conductor->primer_nombre }}</td>
                            <td>{{ $salida->hora_sale->format('H:i') }}</td>
                            <td>
                                @if ($salida->tipo_combustible === 'gas_oil')
                                    <span class="badge badge-info">Gas Oil</span>
                                @else
                                    <span class="badge badge-info">Nafta</span>
                                @endif
                            </td>
                            <td>{{ $salida->kms_sale ?? '-' }}</td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm" wire:click="abrirBoleta({{ $salida->id }})">
                                    <i class="fas fa-check-circle"></i> Cerrar Salida
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $this->salidasPendientes->links() }}
        </div>
    @else
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            No hay salidas pendientes de guardias anteriores.
        </div>
    @endif

    {{-- Modal Boleta de Cierre --}}
    @if ($mostrarBoleta && $salidaPendiente)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-check-circle"></i> Cerrar Salida - {{ $salidaPendiente->vehiculo->matricula }}
                        </h5>
                        <button type="button" class="close text-white" wire:click="cerrarBoleta">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Fecha de salida:</strong> {{ $salidaPendiente->guardia->date->format('d/m/Y') }}<br>
                            <strong>Hora de salida:</strong> {{ $salidaPendiente->hora_sale->format('H:i') }}<br>
                            <strong>Km de salida:</strong> {{ $salidaPendiente->kms_sale }}<br>
                            <strong>Conductor:</strong> {{ $salidaPendiente->conductor->primer_apellido }}, {{ $salidaPendiente->conductor->primer_nombre }}
                        </div>

                        <div class="form-group">
                            <label>Hora de retorno *</label>
                            <input type="text" class="form-control" placeholder="HH:MM"
                                   wire:model="boleta_hora_entra" maxlength="5">
                            @error('boleta_hora_entra') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Km de retorno *</label>
                            <input type="number" class="form-control" wire:model="boleta_kms_entra" min="0">
                            <small class="text-muted">Debe ser mayor o igual a {{ $salidaPendiente->kms_sale }} km</small>
                            @error('boleta_kms_entra') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea class="form-control" wire:model="boleta_observaciones" rows="3"
                                      placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarBoleta">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-success" wire:click="guardarBoleta">
                            <i class="fas fa-check"></i> Confirmar Cierre
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
