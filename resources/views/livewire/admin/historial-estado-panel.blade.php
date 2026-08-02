<div>
    <div class="card card-outline-ops">
        <div class="card-header-ops">
            <div class="card-header-ops__title-wrap">
                <h3 class="card-title-ops mb-0"><i class="fas fa-id-badge"></i> Altas / Bajas (Ejército)</h3>
            </div>
            <div class="card-tools">
                <span class="badge-ops {{ $user->estaActivoEnElEjercito() ? 'badge-ops-success' : 'badge-ops-danger' }}">
                    {{ $user->estaActivoEnElEjercito() ? 'Activo' : 'De baja' }}
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($this->historial->isNotEmpty())
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-ops">
                        <tr>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->historial as $registro)
                            <tr wire:key="historial-estado-{{ $registro->id }}">
                                <td>
                                    @if ($registro->tipo === 'alta')
                                        <span class="badge-ops badge-ops-success">Alta</span>
                                    @else
                                        <span class="badge-ops badge-ops-danger">Baja</span>
                                    @endif
                                </td>
                                <td>{{ $registro->fecha->format('d/m/Y') }}</td>
                                <td>{{ $registro->motivo ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-id-badge fa-2x d-block mb-2"></i>
                    Este usuario todavía no tiene movimientos de alta/baja registrados.
                </div>
            @endif
        </div>

        @if ($this->puedeEditar())
            <div class="card-footer text-right">
                @if ($this->proximoTipo === 'alta' && $this->altasRestantes === 0)
                    <span class="text-danger small">
                        <i class="fas fa-ban"></i> Este usuario agotó el máximo de ingresos ({{ \App\Models\HistorialEstado::MAX_ALTAS }}). No se permite un nuevo reingreso.
                    </span>
                @else
                    <button type="button" class="btn btn-sm {{ $this->proximoTipo === 'baja' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                        wire:click="abrirForm">
                        @if ($this->proximoTipo === 'baja')
                            <i class="fas fa-user-slash"></i> Dar de baja
                        @else
                            <i class="fas fa-user-check"></i> Registrar reingreso
                            <small class="d-block">({{ $this->altasRestantes }} disponible{{ $this->altasRestantes === 1 ? '' : 's' }})</small>
                        @endif
                    </button>
                @endif
            </div>
        @endif
    </div>

    {{-- Panel pantalla completa: registrar alta o baja --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalHistorialEstado" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="guardar" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Altas / Bajas</span>
                        <h5 class="ops-panel__title">
                            {{ $this->proximoTipo === 'baja' ? 'Registrar Baja' : 'Registrar Reingreso' }}
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalHistorialEstado')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        @error('tipo') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror

                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" wire:model="fecha" class="form-control @error('fecha') is-invalid @enderror">
                            @error('fecha') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
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
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalHistorialEstado')">Cancelar</button>
                    <button type="submit" class="btn {{ $this->proximoTipo === 'baja' ? 'btn-danger' : 'btn-success' }}"
                        wire:loading.attr="disabled" wire:target="guardar">
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

        $wire.on('abrir-modal-historial-estado', () => {
            document.getElementById('modalHistorialEstado').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        $wire.on('cerrar-modal-historial-estado', () => {
            cerrarOpsPanel('modalHistorialEstado');
        });
    </script>
@endscript