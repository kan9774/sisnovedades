<div>
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tools text-info"></i> Mantenimientos
            </h3>
            <div class="card-tools">
                @can('create', App\Models\MantenimientoVehiculo::class)
                    <button type="button" wire:click="abrir" class="btn btn-outline-primary btn-sm"
                        style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                        <i class="fas fa-plus-circle"></i> Registrar Mantenimiento
                    </button>
                @endcan
                @if (count($items))
                    <a href="{{ route('admin.vehiculos.mantenimientos.index', $vehiculo) }}"
                        class="btn btn-outline-secondary btn-sm"
                        style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                        Ver todos
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Km</th>
                        <th>Descripción</th>
                        <th>Costo</th>
                        <th>Taller</th>
                        <th>Próximo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr wire:key="mant-{{ $item->id }}">
                            <td>{{ $item->fecha->format('d/m/Y') }}</td>
                            <td><span class="badge badge-secondary">{{ $item->tipo_label }}</span></td>
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
                            <td class="text-right">
                                @can('update', $item)
                                    <button type="button" class="btn btn-outline-warning btn-xs"
                                        wire:click="editar({{ $item->id }})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endcan
                                @can('delete', $item)
                                    <button type="button" class="btn btn-outline-danger btn-xs"
                                        wire:click="eliminar({{ $item->id }})"
                                        wire:confirm="¿Eliminar este mantenimiento?" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
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
    </div>

    @if ($abierto)
        <div class="modal d-block" style="background: rgba(0,0,0,.5)" wire:click.self="cerrar"
            wire:keydown.escape="cerrar">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form wire:submit.prevent="guardar">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-tools text-info"></i>
                                {{ $editandoId ? 'Editar Mantenimiento' : 'Registrar Mantenimiento' }}:
                                {{ $vehiculo->matricula }}
                            </h5>
                            <button type="button" class="close" wire:click="cerrar"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
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
                                <textarea wire:model="descripcion" rows="3" class="form-control @error('descripcion') is-invalid @enderror"
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
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                wire:click="cerrar">Cancelar</button>
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-save"></i>
                                {{ $editandoId ? 'Guardar cambios' : 'Guardar Mantenimiento' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
