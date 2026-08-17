<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <x-ops-card title="Unidades individuales" eyebrow="BCOM1 · Inventario" icon="barcode">
        <x-slot:actions>
            @can('create', \App\Models\ItemUnidad::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="abrirModalAlta">
                    Nueva unidad
                </x-btn-ops>
            @endcan
        </x-slot:actions>

        <x-slot:header>
            <div class="row align-items-center">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.400ms="busqueda" class="form-control"
                        placeholder="Buscar por nº de serie...">
                </div>
                <div class="col-md-4">
                    <select wire:model.live="filtroItemId" class="form-control">
                        <option value="">Todos los ítems</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="filtroEstado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="disponible">Disponible</option>
                        <option value="asignado">Asignado</option>
                        <option value="en_reparacion">En reparación</option>
                        <option value="baja">Dado de baja</option>
                    </select>
                </div>
            </div>
        </x-slot:header>

        <div class="table-responsive">
            <table class="table table-ops-hover mb-0">
                <thead class="thead-ops">
                    <tr>
                        <th>Nº de serie</th>
                        <th>Ítem</th>
                        <th>Estado</th>
                        <th>Vencimiento</th>
                        <th>Ubicación actual</th>
                        <th>Responsable</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unidades as $unidad)
                        <tr wire:key="unidad-{{ $unidad->id }}">
                            <td>{{ $unidad->numero_serie ?? '—' }}</td>
                            <td>{{ $unidad->item->nombre }}</td>
                            <td>
                                <span
                                    class="badge-ops
                                    @switch($unidad->estado)
                                        @case('disponible') badge-ops-success @break
                                        @case('asignado') badge-ops-info @break
                                        @case('en_reparacion') badge-ops-warning @break
                                        @case('baja') badge-ops-secondary @break
                                    @endswitch
                                ">
                                    {{ str_replace('_', ' ', ucfirst($unidad->estado)) }}
                                </span>
                            </td>
                            <td>
                                @if ($unidad->vencimiento)
                                    @if ($unidad->estaVencida())
                                        <span class="badge-ops badge-ops-danger"
                                            title="Venció el {{ $unidad->vencimiento->format('d/m/Y') }}">
                                            <i class="fas fa-exclamation-triangle"></i> Vencida
                                        </span>
                                    @else
                                        <span
                                            class="text-muted small">{{ $unidad->vencimiento->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $unidad->ubicacionActual->nombre ?? '—' }}</td>
                            <td>{{ $unidad->responsable->name ?? '—' }}</td>
                            <td class="text-right">
                                @if ($unidad->estado !== 'baja')
                                    <x-ops-actions>
                                        @can('asignar', $unidad)
                                            <x-btn-ops variant="info" icon="exchange-alt" class="btn-ops-icon btn-ops-icon--sm"
                                                wire:click="abrirModalAsignar({{ $unidad->id }})"
                                                title="Asignar / transferir" />
                                        @endcan

                                        @can('marcarEnReparacion', $unidad)
                                            @if ($unidad->estado !== 'en_reparacion')
                                                @if ($unidad->ubicacionActual?->es_general)
                                                    <x-btn-ops variant="warning" icon="tools" class="btn-ops-icon btn-ops-icon--sm"
                                                        x-on:click="
                                                            confirmarAccion({
                                                                title: '¿Enviar a reparación?',
                                                                text: '¿Marcar esta unidad como en reparación?',
                                                                confirmButtonText: 'Sí, enviar',
                                                                onConfirm: () => $wire.marcarEnReparacion({{ $unidad->id }}),
                                                            })
                                                        "
                                                        title="Enviar a reparación" />
                                                @else
                                                    <x-btn-ops variant="warning" icon="tools" class="btn-ops-icon btn-ops-icon--sm"
                                                        disabled
                                                        title="Solo se puede enviar a reparación desde el Depósito General" />
                                                @endif
                                            @endif
                                        @endcan

                                        @can('volverDeReparacion', $unidad)
                                            @if ($unidad->estado === 'en_reparacion')
                                                <x-btn-ops variant="success" icon="check-circle" class="btn-ops-icon btn-ops-icon--sm"
                                                    x-on:click="
                                                        confirmarAccion({
                                                            title: '¿Volver de reparación?',
                                                            text: '¿Marcar esta unidad como disponible nuevamente?',
                                                            confirmButtonText: 'Sí, marcar disponible',
                                                            onConfirm: () => $wire.volverDeReparacion({{ $unidad->id }}),
                                                        })
                                                    "
                                                    title="Volver de reparación (disponible)" />
                                            @endif
                                        @endcan

                                        @can('darDeBaja', $unidad)
                                            @if ($unidad->estado === 'en_reparacion' || $unidad->ubicacionActual?->es_general)
                                                <x-btn-ops variant="danger" icon="trash" class="btn-ops-icon btn-ops-icon--sm"
                                                    wire:click="abrirModalBaja({{ $unidad->id }})"
                                                    title="Dar de baja" />
                                            @else
                                                <x-btn-ops variant="danger" icon="trash" class="btn-ops-icon btn-ops-icon--sm"
                                                    disabled
                                                    title="Solo se puede dar de baja desde el Depósito General" />
                                            @endif
                                        @endcan
                                    </x-ops-actions>
                                @else
                                    <span class="text-muted small">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No hay unidades registradas todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-slot:footer>
            {{ $unidades->links() }}
        </x-slot:footer>
    </x-ops-card>

    {{-- Panel: alta de unidad (estilo ops) --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalUnidadAlta" wire:ignore.self>
            <div class="ops-panel">
                <form wire:submit="darDeAlta" class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">BCOM1 · Inventario</span>
                            <h5 class="ops-panel__title">Nueva unidad</h5>
                        </div>
                        <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalUnidadAlta')"
                            title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body">
                        <div class="ops-panel__content">
                            <div class="form-group">
                                <label>Ítem</label>
                                <select wire:model.live="altaItemId"
                                    class="form-control @error('altaItemId') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('altaItemId')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Número de serie (opcional)</label>
                                <input type="text" wire:model="altaNumeroSerie" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Ubicación inicial</label>
                                <select wire:model="altaUbicacionId"
                                    class="form-control @error('altaUbicacionId') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('altaUbicacionId')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Proveedor (opcional)</label>
                                    <select wire:model="altaProveedorId"
                                        class="form-control @error('altaProveedorId') is-invalid @enderror">
                                        <option value="">Sin especificar</option>
                                        @foreach ($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('altaProveedorId')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Fecha de recibido (opcional)</label>
                                    <input type="date" wire:model="altaFechaRecibido"
                                        class="form-control @error('altaFechaRecibido') is-invalid @enderror">
                                    @error('altaFechaRecibido')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    @if ($this->itemSeleccionadoTieneVidaUtil())
                                        <small class="form-text text-muted">
                                            Este ítem vence a los {{ $this->vidaUtilDelItemSeleccionado() }} meses de
                                            recibido.
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Motivo (opcional)</label>
                                <input type="text" wire:model="altaMotivo" class="form-control"
                                    placeholder="ej: compra, donación...">
                            </div>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <x-btn-ops variant="secondary" type="button" class="footer-btn"
                            onclick="cerrarOpsPanel('modalUnidadAlta')">
                            Cancelar
                        </x-btn-ops>
                        <x-btn-ops variant="primary" type="submit" wire:loading.attr="disabled"
                            wire:target="darDeAlta">
                            <span wire:loading.remove wire:target="darDeAlta"><i class="fas fa-save"></i> Dar de
                                alta</span>
                            <span wire:loading wire:target="darDeAlta"><i class="fas fa-spinner fa-spin"></i>
                                Guardando...</span>
                        </x-btn-ops>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Panel: asignar / transferir (estilo ops) --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalUnidadAsignar" wire:ignore.self>
            <div class="ops-panel">
                <form wire:submit="asignar" class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">BCOM1 · Inventario</span>
                            <h5 class="ops-panel__title">Asignar / transferir unidad</h5>
                        </div>
                        <button type="button" class="ops-panel__close"
                            onclick="cerrarOpsPanel('modalUnidadAsignar')" title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body">
                        <div class="ops-panel__content">
                            <div class="form-group">
                                <label>Nueva ubicación</label>
                                <select wire:model="asignarUbicacionId"
                                    class="form-control @error('asignarUbicacionId') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($ubicaciones as $ubicacion)
                                        <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('asignarUbicacionId')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Responsable</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }} {{ auth()->user()->last_name }}"
                                    disabled>
                                <small class="form-text text-muted">Se asigna automáticamente a quien realiza la
                                    operación.</small>
                            </div>
                            <div class="form-group">
                                <label>Motivo (opcional)</label>
                                <input type="text" wire:model="asignarMotivo" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <x-btn-ops variant="secondary" type="button" class="footer-btn"
                            onclick="cerrarOpsPanel('modalUnidadAsignar')">
                            Cancelar
                        </x-btn-ops>
                        <x-btn-ops variant="primary" type="submit" wire:loading.attr="disabled"
                            wire:target="asignar">
                            <span wire:loading.remove wire:target="asignar"><i class="fas fa-save"></i> Asignar</span>
                            <span wire:loading wire:target="asignar"><i class="fas fa-spinner fa-spin"></i>
                                Guardando...</span>
                        </x-btn-ops>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Panel: dar de baja (estilo ops) --}}
    <template x-teleport="body">
        <div class="ops-panel-overlay" id="modalUnidadBaja" wire:ignore.self>
            <div class="ops-panel">
                <form wire:submit="confirmarBaja" class="ops-panel__form">
                    <div class="ops-panel__header">
                        <div class="ops-panel__title-wrap">
                            <span class="ops-panel__eyebrow">BCOM1 · Inventario</span>
                            <h5 class="ops-panel__title">Dar de baja unidad</h5>
                        </div>
                        <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalUnidadBaja')"
                            title="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-panel__body">
                        <div class="ops-panel__content">
                            <p class="text-muted">Esta acción es definitiva: la unidad queda fuera de servicio.</p>
                            <div class="form-group">
                                <label>Motivo</label>
                                <textarea wire:model="bajaMotivo" class="form-control @error('bajaMotivo') is-invalid @enderror" rows="3"></textarea>
                                @error('bajaMotivo')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="ops-panel__footer">
                        <x-btn-ops variant="secondary" type="button" class="footer-btn"
                            onclick="cerrarOpsPanel('modalUnidadBaja')">
                            Cancelar
                        </x-btn-ops>
                        <x-btn-ops variant="danger" type="submit" wire:loading.attr="disabled"
                            wire:target="confirmarBaja">
                            <span wire:loading.remove wire:target="confirmarBaja"><i class="fas fa-trash"></i> Dar de
                                baja</span>
                            <span wire:loading wire:target="confirmarBaja"><i class="fas fa-spinner fa-spin"></i>
                                Guardando...</span>
                        </x-btn-ops>
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
                if (overlay) overlay.classList.remove('is-open');
                document.body.classList.remove('ops-panel-open');
            };
        }

        const opsPanelesUnidad = {
            'abrir-modal-unidad-alta': 'modalUnidadAlta',
            'abrir-modal-unidad-asignar': 'modalUnidadAsignar',
            'abrir-modal-unidad-baja': 'modalUnidadBaja',
        };

        Object.entries(opsPanelesUnidad).forEach(([evento, id]) => {
            $wire.on(evento, () => {
                document.getElementById(id).classList.add('is-open');
                document.body.classList.add('ops-panel-open');
            });
        });

        $wire.on('cerrar-modal-unidad', () => {
            cerrarOpsPanel('modalUnidadAlta');
            cerrarOpsPanel('modalUnidadAsignar');
            cerrarOpsPanel('modalUnidadBaja');
        });
    </script>
@endscript