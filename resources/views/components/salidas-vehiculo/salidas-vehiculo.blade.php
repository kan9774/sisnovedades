<div>
    @if ($guardia->status === 'open' && $puedeOperarGuardia)
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-outline-info btn-sm" wire:click="abrirCrear">
                <i class="fas fa-plus-circle"></i> Registrar Salida
            </button>
        </div>
    @endif

    @if ($this->salidas->total() > 0)
        <div wire:poll.5s="refreshSalidas">
        <table class="table table-striped table-hover mb-0" style="width: 100%">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Vehículo</th>
                    <th>Conductor</th>
                    <th>Combustible</th>
                    <th>Hora Sale</th>
                    <th>Hora Entra</th>
                    <th>Estado</th>
                    <th>Km Rec.</th>
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
                        <td>{{ $salida->hora_sale?->format('H:i') }}<br><small class="text-muted">{{ $salida->guardia->date->format('d/m/Y') }}</small></td>
                        <td>{{ $salida->hora_entra?->format('H:i') ?? '-' }}</td>
                        <td>
                            @if ($salida->tiene_boleta)
                                <span class="badge badge-success">✅ Cerrada</span>
                                @if ($salida->boletaCierre)
                                    <br><small class="text-muted">{{ $salida->boletaCierre->fecha_entra->format('d/m/Y')  }}</small>
                                @else
                               <small class="text-muted"> {{ $salida->guardia->date->format('d/m/Y') }} </small>
                                @endif
                            @else
                                <span class="badge badge-warning">⚠️ Pendiente</span>
                            @endif
                        </td>
                        <td>{{ $salida->kms_recorridos ?? '-' }}</td>
                        <td>{{ $salida->litros ? number_format($salida->litros, 2) : '-' }}</td>
                        <td class="text-center align-middle">
                            @if ($guardia->status === 'open' && $puedeOperarGuardia && $salida->guardia_id === $guardia->id)
                                <div class="d-flex justify-content-center">
                                    <button type="button" wire:click="abrirEditar({{ $salida->id }})"
                                        class="btn btn-outline-warning btn-xs mr-1" title="Editar salida">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if (!$salida->tiene_boleta)
                                        <button type="button" wire:click="abrirBoleta({{ $salida->id }})"
                                            class="btn btn-outline-primary btn-xs mr-1" title="Cerrar boleta">
                                            <i class="fas fa-file-invoice"></i>
                                        </button>
                                    @elseif ($salida->boletaCierre && $salida->boletaCierre->guardia_id != $guardia->id)
                                        <button type="button" wire:click="abrirBoleta({{ $salida->id }})"
                                            class="btn btn-outline-info btn-xs mr-1" title="Ver/Editar boleta">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                    <button type="button" wire:click="eliminar({{ $salida->id }})"
                                        wire:confirm="¿Eliminar esta salida?" class="btn btn-outline-danger btn-xs" title="Eliminar salida">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @elseif ($salida->guardia_id !== $guardia->id)
                                <span class="badge badge-light border" title="Salió en la guardia del {{ $salida->guardia->date->format('d/m/Y') }}">
                                    <i class="fas fa-undo"></i> Retorno
                                </span>
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
        </div>

        <div class="mt-3">{{ $this->salidas->links() }}</div>
    @else
        <div class="text-center text-muted py-4">
            <i class="fas fa-truck fa-2x d-block mb-2"></i>
            No hay salidas de vehículos registradas en esta guardia.
        </div>
    @endif

    {{-- Panel pantalla completa: crear/editar salida --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalSalida" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="guardar" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Salidas de Vehículos</span>
                        <h5 class="ops-panel__title">{{ $editandoId ? 'Editar Salida' : 'Registrar Salida' }}</h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalSalida')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
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
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalSalida')">Cancelar</button>
                    <button type="submit" class="btn" wire:loading.attr="disabled" wire:target="guardar" style="background: linear-gradient(135deg, #FFD200 0%, #FBCB5B 100%) !important; color: #0B2545 !important; font-weight: 700; box-shadow: 0 2px 8px rgba(255, 210, 0, 0.35) !important; border: none;">
                        <span wire:loading.remove wire:target="guardar"><i class="fas fa-save"></i> Guardar</span>
                        <span wire:loading wire:target="guardar"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- Panel pantalla completa: boleta de cierre --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalBoletaCierre" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="guardarBoleta" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Salidas de Vehículos</span>
                        <h5 class="ops-panel__title">
                            @if ($salida?->boletaCierre)
                                Editar Boleta de Cierre
                            @else
                                Boleta de Cierre
                            @endif
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalBoletaCierre')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        @if ($salida)
                            {{-- Info de la salida --}}
                            <div class="alert alert-info mb-3">
                                <strong>Salida #{{ $salida->id }}</strong> — {{ $salida->vehiculo->matricula }}<br>
                                Conductor: {{ $salida->conductor->nombre_visible }}<br>
                                Salida: {{ $salida->guardia->date->format('d/m/Y') }} a las {{ $salida->hora_sale?->format('H:i') }}<br>
                                @if ($salida->kms_sale)
                                    Km Sale: <strong>{{ $salida->kms_sale }}</strong>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fecha de regreso <span class="text-danger">*</span></label>
                                        <input type="date" wire:model.live="boleta_fecha_entra" class="form-control @error('boleta_fecha_entra') is-invalid @enderror">
                                        @error('boleta_fecha_entra') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Hora de regreso <span class="text-danger">*</span></label>
                                        <input type="time" wire:model.live="boleta_hora_entra" class="form-control @error('boleta_hora_entra') is-invalid @enderror">
                                        @error('boleta_hora_entra') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Km al regreso <span class="text-danger">*</span></label>
                                        <input type="number" min="0" wire:model.live="boleta_kms_entra" class="form-control @error('boleta_kms_entra') is-invalid @enderror">
                                        @error('boleta_kms_entra') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Observaciones</label>
                                        <input type="text" wire:model.live="boleta_observaciones" class="form-control @error('boleta_observaciones') is-invalid @enderror" maxlength="500">
                                        @error('boleta_observaciones') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Preview del cálculo --}}
                            @if ($boleta_kms_entra && $salida->kms_sale && $boleta_kms_entra > $salida->kms_sale)
                            <div class="alert alert-success mt-3">
                                <strong>Cálculo automático:</strong><br>
                                Kms Recorridos: <strong>{{ $boleta_kms_entra - $salida->kms_sale }}</strong> km
                                @if ($salida->vehiculo && $salida->vehiculo->consumo_litros_por_km)
                                    <br>Litros estimados: <strong>{{ number_format(($boleta_kms_entra - $salida->kms_sale) * $salida->vehiculo->consumo_litros_por_km, 2) }} L</strong>
                                @endif
                            </div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalBoletaCierre')">Cancelar</button>
                    <button type="submit" class="btn" wire:loading.attr="disabled" wire:target="guardarBoleta" style="background: linear-gradient(135deg, #FFD200 0%, #FBCB5B 100%) !important; color: #0B2545 !important; font-weight: 700; box-shadow: 0 2px 8px rgba(255, 210, 0, 0.35) !important; border: none;">
                        <span wire:loading.remove wire:target="guardarBoleta"><i class="fas fa-save"></i> Guardar Boleta</span>
                        <span wire:loading wire:target="guardarBoleta"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>
</div>

<style>
    .ops-panel-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1060;
        background: #f4f5f7;
    }

    .ops-panel-overlay.is-open {
        display: block;
        animation: opsPanelFadeIn .16s ease-out;
    }

    .ops-panel {
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
    }

    .ops-panel__form {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .ops-panel__header {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.75rem;
        background: linear-gradient(135deg, #0B2545 0%, #0F3460 100%);
        border-bottom: 4px solid #FFD200;
    }

    .ops-panel__eyebrow {
        display: block;
        color: #FFD200;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .ops-panel__title {
        color: #fff;
        margin: 0;
        font-weight: 600;
    }

    .ops-panel__close {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
        border-radius: 6px;
        width: 38px;
        height: 38px;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s, border-color .15s;
    }

    .ops-panel__close:hover {
        background: rgba(255, 210, 0, 0.18);
        border-color: #FFD200;
        color: #FFD200;
    }

    .ops-panel__body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 2rem 1.75rem;
    }

    .ops-panel__content {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        border-radius: 10px;
        padding: 1.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .ops-panel__footer {
        flex: 0 0 auto;
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        padding: 1rem 1.75rem;
        background: #fff;
        border-top: 1px solid #e5e7eb;
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.04);
    }

    @keyframes opsPanelFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    body.ops-panel-open {
        overflow: hidden;
    }
</style>

@script
    <script>
        if (!window.cerrarOpsPanel) {
            window.cerrarOpsPanel = function (id) {
                const overlay = document.getElementById(id);
                if (overlay) {
                    overlay.classList.remove('is-open');
                }
                document.body.classList.remove('ops-panel-open');
            };
        }

        $wire.on('abrir-modal-salida', () => {
            document.getElementById('modalSalida').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        $wire.on('cerrar-modal-salida', () => {
            cerrarOpsPanel('modalSalida');
        });

        $wire.on('abrir-modal-boleta', () => {
            document.getElementById('modalBoletaCierre').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        $wire.on('cerrar-modal-boleta', () => {
            cerrarOpsPanel('modalBoletaCierre');
        });
    </script>
@endscript