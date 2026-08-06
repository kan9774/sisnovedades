<div>
    <div class="ops-panel">
        <h5 class="mb-3">Contratos de Servicio Militar (C.S.M.)</h5>

        @if (!$this->perteneceAUnidad)
            <p class="text-muted">
                Los contratos C.S.M. solo aplican a usuarios de B.Com.Nº1.
            </p>
        @elseif (empty($this->plazosDisponibles))
            <p class="text-muted">
                Todavía no hay plantillas de Anexo cargadas en <code>storage/app/csm-plantillas</code>.
            </p>
        @else
            <div class="csm-plazos-grid">
                @foreach ($this->plazosDisponibles as $anios => $etiqueta)
                    <button
                        type="button"
                        class="csm-plazo-btn"
                        wire:click="abrirModal({{ $anios }})"
                    >
                        <span class="csm-plazo-btn__anios">{{ $anios }}</span>
                        <span class="csm-plazo-btn__label">{{ $anios == 1 ? 'año' : 'años' }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal fecha de firma --}}
    @if ($mostrarModal)
        <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Generar contrato — {{ $aniosSeleccionados == 1 ? '1 año' : $aniosSeleccionados . ' años' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="cerrarModal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Fecha de firma</label>
                        <input
                            type="date"
                            class="form-control"
                            wire:model="fechaFirma"
                        >
                        @error('fechaFirma')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        <p class="text-muted small mt-3 mb-0">
                            La autoridad firmante se completa automáticamente con el
                            Jefe de Unidad vigente a esa fecha.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cerrarModal">
                            Cancelar
                        </button>
                        <button type="button" class="btn-ops btn-ops-primary" wire:click="generar">
                            <span wire:loading.remove wire:target="generar">Generar y descargar</span>
                            <span wire:loading wire:target="generar">Generando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>