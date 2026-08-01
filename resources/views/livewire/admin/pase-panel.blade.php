<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Pases</h3>
            <div class="card-tools">
                <span class="badge badge-info">
                    Unidad vigente: {{ $user->paseVigente()?->unidad->nombre ?? $user->unidad?->nombre ?? '—' }}
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($this->historial->isNotEmpty())
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Unidad</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>N° Orden</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->historial as $registro)
                            <tr wire:key="pase-{{ $registro->id }}">
                                <td>{{ $registro->unidad->nombre ?? '—' }}</td>
                                <td>{{ $registro->fecha_desde->format('d/m/Y') }}</td>
                                <td>
                                    @if ($registro->fecha_hasta)
                                        {{ $registro->fecha_hasta->format('d/m/Y') }}
                                    @else
                                        <span class="badge badge-success">Vigente</span>
                                    @endif
                                </td>
                                <td>{{ $registro->numero_orden ?? '—' }}</td>
                                <td>{{ $registro->motivo ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-map-marker-alt fa-2x d-block mb-2"></i>
                    Este usuario todavía no tiene pases registrados.
                </div>
            @endif
        </div>

        @if ($this->puedeEditar())
            <div class="card-footer text-right">
                <button type="button" class="btn btn-outline-info btn-sm" wire:click="abrirForm">
                    <i class="fas fa-exchange-alt"></i> Registrar Pase
                </button>
            </div>
        @endif
    </div>

    {{-- Panel pantalla completa: registrar pase --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalPase" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="guardar" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Pases</span>
                        <h5 class="ops-panel__title">Registrar Pase</h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalPase')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        <div class="form-group">
                            <label>Fecha en que se produce el pase</label>
                            <input type="date" wire:model="fecha" class="form-control @error('fecha') is-invalid @enderror">
                            @error('fecha') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            <small class="text-muted">
                                La unidad anterior se cierra el último día de este mes; la nueva arranca el 1° del mes siguiente.
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Unidad de destino</label>
                            <select wire:model="unidad_id" class="form-control @error('unidad_id') is-invalid @enderror">
                                <option value="">-- Seleccionar --</option>
                                @foreach ($this->unidades as $unidad)
                                    <option value="{{ $unidad->id }}">{{ $unidad->nombre }}</option>
                                @endforeach
                            </select>
                            @error('unidad_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>N° de Orden</label>
                            <input type="text" wire:model="numero_orden" maxlength="50" placeholder="O.B. N° 006/2026"
                                class="form-control @error('numero_orden') is-invalid @enderror">
                            <small class="text-muted">Ej: O.B. N° 006/2026, Minuta N° 015/2026</small>
                            @error('numero_orden') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Motivo <small class="text-muted">(opcional)</small></label>
                            <input type="text" wire:model="motivo" maxlength="255"
                                class="form-control @error('motivo') is-invalid @enderror">
                            @error('motivo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalPase')">Cancelar</button>
                    <button type="submit" class="btn btn-info" wire:loading.attr="disabled" wire:target="guardar">
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
                if (overlay) {
                    overlay.classList.remove('is-open');
                }
                document.body.classList.remove('ops-panel-open');
            };
        }

        $wire.on('abrir-modal-pase', () => {
            document.getElementById('modalPase').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        $wire.on('cerrar-modal-pase', () => {
            cerrarOpsPanel('modalPase');
        });
    </script>
@endscript