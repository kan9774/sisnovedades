<div>
    <div class="card card-outline-ops">
        <div class="card-header-ops">
            <div class="card-header-ops__title-wrap">
                <h3 class="card-title-ops mb-0"><i class="fas fa-history"></i> Historial de Grados</h3>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($this->historial->isNotEmpty())
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-ops">
                        <tr>
                            <th>Grado</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>N° Orden</th>
                            <th>Resolución</th>
                            <th>Observaciones</th>
                            @if ($this->puedeEditar())
                                <th class="text-center">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->historial as $registro)
                            <tr wire:key="historial-grado-{{ $registro->id }}">
                                <td>{{ $registro->grado->nombre ?? '—' }}</td>
                                <td>
                                    @if ($registro->tipo === 'ascenso')
                                        <span class="badge-ops badge-ops-success">Ascenso</span>
                                    @else
                                        <span class="badge-ops badge-ops-danger">Degradación</span>
                                    @endif
                                </td>
                                <td>{{ $registro->fecha_cambio->format('d/m/Y') }}</td>
                                <td>{{ $registro->numero_orden ?? '—' }}</td>
                                <td>{{ $registro->resolucion ?? '—' }}</td>
                                <td>{{ $registro->observaciones ?? '—' }}</td>
                                @if ($this->puedeEditar())
                                    <td class="text-center align-middle">
                                        <x-btn-ops wire:click="abrirEditar({{ $registro->id }})" variant="warning" icon="edit"
                                            class="btn-sm" title="Editar datos del cambio de grado">
                                        </x-btn-ops>
                                        <x-btn-ops wire:click="eliminar({{ $registro->id }})"
                                            wire:confirm="¿Seguro que querés eliminar este movimiento? Si es el más reciente, el grado actual del usuario volverá al anterior."
                                            variant="danger" icon="trash" class="btn-sm">
                                        </x-btn-ops>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-history fa-2x d-block mb-2"></i>
                    Este usuario todavía no tiene historial de grados registrado.
                </div>
            @endif
        </div>
    </div>

    {{-- Panel pantalla completa: editar datos del cambio de grado --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalHistorialGrado" wire:ignore.self>
            <div class="ops-panel">
                <form wire:submit="guardar" class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">BCOM1 · Historial de Grados</span>
                            <h5 class="ops-panel__title">Editar Cambio de Grado</h5>
                        </div>
                        <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalHistorialGrado')"
                            title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body">
                        <div class="ops-panel__content">
                            <div class="form-group">
                                <label>N° de Orden</label>
                                <input type="text" wire:model="numero_orden" maxlength="50" placeholder="016/2026"
                                    class="form-control @error('numero_orden') is-invalid @enderror">
                                <small class="text-muted">Formato: número/año, ej. 016/2026</small>
                                @error('numero_orden')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Resolución</label>
                                <input type="text" wire:model="resolucion" maxlength="255"
                                    class="form-control @error('resolucion') is-invalid @enderror">
                                @error('resolucion')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Observaciones</label>
                                <textarea wire:model="observaciones" rows="3" maxlength="500"
                                    class="form-control @error('observaciones') is-invalid @enderror"></textarea>
                                @error('observaciones')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="cerrarOpsPanel('modalHistorialGrado')">Cancelar</button>
                        <button type="submit" class="btn" wire:loading.attr="disabled" wire:target="guardar"
                            style="background: linear-gradient(135deg, #FFD200 0%, #FBCB5B 100%) !important; color: #0B2545 !important; font-weight: 700; box-shadow: 0 2px 8px rgba(255, 210, 0, 0.35) !important; border: none;">
                            <span wire:loading.remove wire:target="guardar"><i class="fas fa-save"></i> Guardar</span>
                            <span wire:loading wire:target="guardar"><i class="fas fa-spinner fa-spin"></i>
                                Guardando...</span>
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
            window.cerrarOpsPanel = function(id) {
                const overlay = document.getElementById(id);
                if (overlay) {
                    overlay.classList.remove('is-open');
                }
                document.body.classList.remove('ops-panel-open');
            };
        }

        $wire.on('abrir-modal-historial-grado', () => {
            document.getElementById('modalHistorialGrado').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        $wire.on('cerrar-modal-historial-grado', () => {
            cerrarOpsPanel('modalHistorialGrado');
        });
    </script>
@endscript
