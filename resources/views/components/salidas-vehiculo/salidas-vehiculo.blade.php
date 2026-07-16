<div>
    @if ($guardia->status === 'open' && $puedeOperarGuardia)
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-outline-info btn-sm" wire:click="abrirCrear">
                <i class="fas fa-plus-circle"></i> Registrar Salida
            </button>
        </div>
    @endif

    @if ($this->salidas->total() > 0)
        <table class="table table-striped table-hover mb-0" style="width: 100%">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Vehículo</th>
                    <th>Conductor</th>
                    <th>Combustible</th>
                    <th>Hora Sale</th>
                    <th>Hora Entra</th>
                    <th>Km</th>
                    <th>Litros</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->salidas as $index => $salida)
                    <tr wire:key="salida-{{ $salida->id }}">
                        <td>{{ $index + 1 + ($this->salidas->currentPage() - 1) * $this->salidas->perPage() }}</td>
                        <td>
                            @if ($salida->vehiculo)
                                <strong>{{ $salida->vehiculo->matricula }}</strong>
                                @if ($salida->vehiculo->sin_cuentakilometros)
                                    <span class="badge badge-danger badge-pill">S/C</span>
                                @endif
                            @else
                                <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Vehículo eliminado</span>
                            @endif
                        </td>
                        <td>{{ $salida->conductor ? $salida->conductor->nombre_visible : 'Conductor eliminado' }}</td>
                        <td>
                            @if ($salida->tipo_combustible === 'gas_oil')
                                <span class="badge badge-warning">Gas Oil</span>
                            @else
                                <span class="badge badge-info">Nafta</span>
                            @endif
                        </td>
                        <td>{{ $salida->hora_sale?->format('H:i') }}</td>
                        <td>{{ $salida->hora_entra?->format('H:i') ?? '-' }}</td>
                        <td>{{ $salida->kms_recorridos ?? '-' }}</td>
                        <td>{{ $salida->litros ? number_format($salida->litros, 2) : '-' }}</td>
                        <td class="text-center align-middle">
                            @if ($guardia->status === 'open' && $puedeOperarGuardia)
                                <div class="d-flex justify-content-center">
                                    <button type="button" wire:click="abrirEditar({{ $salida->id }})"
                                        class="btn btn-outline-warning btn-xs mr-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" wire:click="eliminar({{ $salida->id }})"
                                        wire:confirm="¿Eliminar esta salida?" class="btn btn-outline-danger btn-xs">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            @if ($this->resumenCombustible->isNotEmpty())
                <tfoot>
                    @foreach ($this->resumenCombustible as $resumen)
                        <tr class="font-weight-bold" style="background: #f8f9fa;">
                            <td colspan="6" class="text-right">
                                TOTAL {{ $resumen->tipo_combustible === 'gas_oil' ? 'Gas Oil' : 'Nafta' }}:
                            </td>
                            <td>{{ $resumen->total_kms ?? 0 }}</td>
                            <td>{{ number_format($resumen->total_litros ?? 0, 2) }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tfoot>
            @endif
        </table>

        <div class="mt-3">{{ $this->salidas->links() }}</div>
    @else
        <div class="text-center text-muted py-4">
            <i class="fas fa-truck fa-2x d-block mb-2"></i>
            No hay salidas de vehículos registradas en esta guardia.
        </div>
    @endif

    {{-- Modal --}}
    @if ($showModal)
    <div class="modal d-block" style="background: rgba(255, 255, 255, 0.15) !important; backdrop-filter: blur(12px) saturate(180%) !important; -webkit-backdrop-filter: blur(12px) saturate(180%) !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; border-radius: 16px !important; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;" wire:click.self="cerrarModal" wire:keydown.escape="cerrarModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="backdrop-filter: blur(10px);">
                <form wire:submit="guardar">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editandoId ? 'Editar Salida' : 'Registrar Salida' }}
                        </h5>
                        <button type="button" class="close" wire:click="cerrarModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vehículo <span class="text-danger">*</span></label>
                                    <select wire:model.live="vehiculo_id" class="form-control @error('vehiculo_id') is-invalid @enderror">
                                        <option value="">Seleccionar...</option>
                                        @foreach ($this->vehiculos as $vehiculo)
                                            <option value="{{ $vehiculo->id }}">
                                                {{ $vehiculo->matricula }} - {{ $vehiculo->descripcion }}
                                                @if ($vehiculo->sin_cuentakilometros) (Sin cuentakm) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehiculo_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Conductor <span class="text-danger">*</span></label>
                                    <select wire:model="conductor_id" class="form-control @error('conductor_id') is-invalid @enderror">
                                        <option value="">Seleccionar...</option>
                                        @foreach ($this->conductores as $conductor)
                                            <option value="{{ $conductor->id }}">{{ $conductor->nombre_visible }}</option>
                                        @endforeach
                                    </select>
                                    @error('conductor_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Combustible <span class="text-danger">*</span></label>
                                    <select wire:model.live="tipo_combustible" class="form-control @error('tipo_combustible') is-invalid @enderror">
                                        <option value="">Seleccionar...</option>
                                        <option value="gas_oil">Gas Oil</option>
                                        <option value="nafta">Nafta</option>
                                    </select>
                                    @error('tipo_combustible') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hora Salida <span class="text-danger">*</span></label>
                                    <input type="time" wire:model.live="hora_sale" class="form-control @error('hora_sale') is-invalid @enderror">
                                    @error('hora_sale') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hora Entrada</label>
                                    <input type="time" wire:model.live="hora_entra" class="form-control @error('hora_entra') is-invalid @enderror">
                                    @error('hora_entra') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Km Salida</label>
                                    <input type="number" min="0" wire:model.live="kms_sale" class="form-control @error('kms_sale') is-invalid @enderror">
                                    @error('kms_sale') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Km Entrada</label>
                                    <input type="number" min="0" wire:model.live="kms_entra" class="form-control @error('kms_entra') is-invalid @enderror">
                                    @error('kms_entra') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Comisión / Motivo <span class="text-danger">*</span></label>
                            <textarea wire:model="comision" rows="3" class="form-control @error('comision') is-invalid @enderror"></textarea>
                            @error('comision') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cerrarModal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="guardar" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); border: none;">
                            <span wire:loading.remove wire:target="guardar"><i class="fas fa-save"></i> Guardar</span>
                            <span wire:loading wire:target="guardar"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
</div>