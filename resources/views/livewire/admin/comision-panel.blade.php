<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-clock"></i> Comisiones</h3>
            <div class="card-tools">
                @if ($user->comisionVigente())
                    <span class="badge badge-warning">
                        En comisión en: {{ $user->comisionVigente()->unidad->nombre ?? '—' }}
                    </span>
                @else
                    <span class="badge badge-secondary">Sin comisión vigente</span>
                @endif
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
                            <th>Tipo</th>
                            <th>N° Orden</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->historial as $registro)
                            <tr wire:key="comision-{{ $registro->id }}">
                                <td>{{ $registro->unidad->nombre ?? '—' }}</td>
                                <td>{{ $registro->fecha_inicio->format('d/m/Y') }}</td>
                                <td>
                                    @if ($registro->fecha_fin)
                                        {{ $registro->fecha_fin->format('d/m/Y') }}
                                    @else
                                        <span class="badge badge-success">Vigente</span>
                                    @endif
                                </td>
                                <td>{{ $registro->tipo_orden ?? '—' }}</td>
                                <td>{{ $registro->numero_orden ?? '—' }}</td>
                                <td>{{ $registro->motivo ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-user-clock fa-2x d-block mb-2"></i>
                    Este usuario todavía no tiene comisiones registradas.
                </div>
            @endif
        </div>

        @if ($this->puedeEditar())
            <div class="card-footer text-right">
                @if ($user->comisionVigente())
                    <button type="button" class="btn btn-outline-warning btn-sm" wire:click="abrirFormCierre">
                        <i class="fas fa-flag-checkered"></i> Finalizar Comisión
                    </button>
                @else
                    <button type="button" class="btn btn-outline-info btn-sm" wire:click="abrirForm">
                        <i class="fas fa-user-clock"></i> Iniciar Comisión
                    </button>
                @endif
            </div>
        @endif
    </div>

    {{-- Panel pantalla completa: iniciar comisión --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalComisionAbrir" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="guardar" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Comisiones</span>
                        <h5 class="ops-panel__title">Iniciar Comisión</h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalComisionAbrir')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        <div class="form-group">
                            <label>Fecha de inicio</label>
                            <input type="date" wire:model="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror">
                            @error('fecha_inicio') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Unidad en la que presta comisión</label>
                            <select wire:model="unidad_id" class="form-control @error('unidad_id') is-invalid @enderror">
                                <option value="">-- Seleccionar --</option>
                                @foreach ($this->unidades as $unidad)
                                    <option value="{{ $unidad->id }}">{{ $unidad->nombre }}</option>
                                @endforeach
                            </select>
                            @error('unidad_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            <small class="text-muted">
                                Debe ser una unidad distinta a la unidad formal del usuario ({{ $user->unidad?->nombre ?? '—' }}).
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Tipo de Orden</label>
                            <select wire:model="tipo_orden" class="form-control @error('tipo_orden') is-invalid @enderror">
                                <option value="">-- Seleccionar --</option>
                                @foreach ($this->tiposOrden as $tipo)
                                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                                @endforeach
                            </select>
                            @error('tipo_orden') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>N° de Orden</label>
                            <input type="text" wire:model="numero_orden" maxlength="50" placeholder="O.B. N° 006/2026"
                                class="form-control @error('numero_orden') is-invalid @enderror">
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
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalComisionAbrir')">Cancelar</button>
                    <button type="submit" class="btn btn-info" wire:loading.attr="disabled" wire:target="guardar">
                        <span wire:loading.remove wire:target="guardar"><i class="fas fa-save"></i> Guardar</span>
                        <span wire:loading wire:target="guardar"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    {{-- Panel pantalla completa: finalizar comisión --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalComisionCerrar" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="finalizar" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Comisiones</span>
                        <h5 class="ops-panel__title">Finalizar Comisión</h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalComisionCerrar')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        <div class="form-group">
                            <label>Fecha de finalización</label>
                            <input type="date" wire:model="fecha_fin" class="form-control @error('fecha_fin') is-invalid @enderror">
                            @error('fecha_fin') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalComisionCerrar')">Cancelar</button>
                    <button type="submit" class="btn btn-warning" wire:loading.attr="disabled" wire:target="finalizar">
                        <span wire:loading.remove wire:target="finalizar"><i class="fas fa-flag-checkered"></i> Finalizar</span>
                        <span wire:loading wire:target="finalizar"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
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

        $wire.on('abrir-modal-comision-abrir', () => {
            document.getElementById('modalComisionAbrir').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        $wire.on('cerrar-modal-comision-abrir', () => {
            cerrarOpsPanel('modalComisionAbrir');
        });

        $wire.on('abrir-modal-comision-cerrar', () => {
            document.getElementById('modalComisionCerrar').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        $wire.on('cerrar-modal-comision-cerrar', () => {
            cerrarOpsPanel('modalComisionCerrar');
        });
    </script>
@endscript