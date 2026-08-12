<div>
    <x-ops-card title="Mantenimientos" icon="tools" eyebrow="{{ count($items) }} registros">
        <x-slot name="actions">
            @can('create', App\Models\MantenimientoVehiculo::class)
                <x-btn-ops variant="primary" icon="plus-circle" wire:click="abrir">
                    Registrar Mantenimiento
                </x-btn-ops>
            @endcan
        </x-slot>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-ops">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Km</th>
                        <th>Descripción</th>
                        <th>Costo</th>
                        <th>Taller</th>
                        <th>Próximo</th>
                        <th class="text-center" style="width: 90px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="mant-{{ $item->id }}">
                            <td>{{ $item->fecha->format('d/m/Y') }}</td>
                            <td><span class="badge-ops badge-ops-secondary">{{ $item->tipo_label }}</span></td>
                            <td>{{ $item->kilometraje ?? '-' }}</td>
                            <td>{{ $item->descripcion }}</td>
                            <td>{{ $item->costo ? '$' . number_format($item->costo, 2, ',', '.') : '-' }}</td>
                            <td>{{ $item->taller ?? '-' }}</td>
                            <td>
                                @if ($item->proximo_mantenimiento_fecha)
                                    {{ $item->proximo_mantenimiento_fecha->format('d/m/Y') }}
                                @elseif ($item->proximo_mantenimiento_km)
                                    {{ $item->proximo_mantenimiento_km }} km
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <x-ops-actions
                                    :model="$item"
                                    edit="editar({{ $item->id }})"
                                    delete="eliminar({{ $item->id }})"
                                    :showRestore="false"
                                    size="xs"
                                    deleteTitle="¿Eliminar mantenimiento?"
                                    deleteText="Esta acción no se puede deshacer."
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-tools fa-2x d-block mb-2"></i>
                                No hay mantenimientos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ops-card>

    {{-- MODAL: alta/edición de mantenimiento (apilado sobre el ops-panel de detalle, --}}
    {{-- que YA está teleportado a body — no volver a envolver en x-teleport acá) --}}
    <div class="ops-modal-overlay ops-modal--lg" :class="{ 'is-open': $wire.abierto }"
        wire:click.self="cerrar" wire:keydown.escape="cerrar">
        <div class="ops-modal ops-modal--lg">
                <form wire:submit.prevent="guardar">
                    <div class="ops-modal__header">
                        <h5 class="ops-modal__title">
                            <i class="fas fa-tools"></i>
                            {{ $editandoId ? 'Editar Mantenimiento' : 'Registrar Mantenimiento' }}:
                            {{ $vehiculo->matricula }}
                        </h5>
                        <button type="button" class="ops-modal__close" wire:click="cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="ops-modal__body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo <span class="text-danger">*</span></label>
                                    <select wire:model="tipo"
                                        class="form-control @error('tipo') is-invalid @enderror">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="preventivo">Preventivo</option>
                                        <option value="correctivo">Correctivo</option>
                                        <option value="revision_tecnica">Revisión Técnica</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                    @error('tipo')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="fecha"
                                        class="form-control @error('fecha') is-invalid @enderror">
                                    @error('fecha')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kilometraje <small class="text-muted">(opcional)</small></label>
                                    <input type="number" wire:model="kilometraje" min="0"
                                        class="form-control @error('kilometraje') is-invalid @enderror">
                                    @error('kilometraje')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Costo <small class="text-muted">(opcional)</small></label>
                                    <input type="number" wire:model="costo" step="0.01" min="0"
                                        class="form-control @error('costo') is-invalid @enderror">
                                    @error('costo')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Descripción <span class="text-danger">*</span></label>
                            <textarea wire:model="descripcion" rows="3"
                                class="form-control @error('descripcion') is-invalid @enderror"
                                placeholder="Ej: Cambio de aceite y filtros, revisión de frenos"></textarea>
                            @error('descripcion')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Taller <small class="text-muted">(opcional)</small></label>
                                    <input type="text" wire:model="taller"
                                        class="form-control @error('taller') is-invalid @enderror">
                                    @error('taller')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Próximo mantenimiento (fecha) <small
                                            class="text-muted">(opcional)</small></label>
                                    <input type="date" wire:model="proximo_mantenimiento_fecha"
                                        class="form-control @error('proximo_mantenimiento_fecha') is-invalid @enderror">
                                    @error('proximo_mantenimiento_fecha')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Próximo mantenimiento (km) <small
                                            class="text-muted">(opcional)</small></label>
                                    <input type="number" wire:model="proximo_mantenimiento_km" min="0"
                                        class="form-control @error('proximo_mantenimiento_km') is-invalid @enderror">
                                    @error('proximo_mantenimiento_km')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ops-modal__footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                            wire:click="cerrar">Cancelar</button>
                        <button type="submit" class="btn ops-modal__save-btn btn-sm">
                            <i class="fas fa-save"></i>
                            {{ $editandoId ? 'Guardar cambios' : 'Guardar Mantenimiento' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>