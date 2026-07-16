<div>
    <button type="button" wire:click="abrir" class="btn btn-primary btn-sm" title="Gestionar Tipos de Rodado" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3); border: none;">
        <i class="fas fa-cog"></i>
    </button>

    @if ($abierto)
        <div class="modal d-block" style="background: rgba(255, 255, 255, 0.15) !important; backdrop-filter: blur(12px) saturate(180%) !important; -webkit-backdrop-filter: blur(12px) saturate(180%) !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; border-radius: 16px !important; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;" wire:click.self="cerrar" wire:keydown.escape="cerrar">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
                    <div class="modal-header" style="background-color: #eef2ff !important;">
                        <h5 class="modal-title">Tipos de Rodado</h5>
                        <button type="button" class="close" wire:click="cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="guardar" class="row mb-3">
                            <div class="col-md-4 form-group">
                                <label class="mb-1">Nombre <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nombre"
                                    class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                                    placeholder="Ej: Trasera moto 90/90">
                                @error('nombre')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="mb-1">Medida</label>
                                <input type="text" wire:model="medida"
                                    class="form-control form-control-sm @error('medida') is-invalid @enderror"
                                    placeholder="90/90-19">
                                @error('medida')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="mb-1">Posición <span class="text-danger">*</span></label>
                                <select wire:model="posicion"
                                    class="form-control form-control-sm @error('posicion') is-invalid @enderror">
                                    <option value="unico">Único</option>
                                    <option value="delantero">Delantero</option>
                                    <option value="trasero">Trasero</option>
                                </select>
                                @error('posicion')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="mb-1">Marca</label>
                                <input type="text" wire:model="marca"
                                    class="form-control form-control-sm @error('marca') is-invalid @enderror"
                                    placeholder="Ej: Pirelli">
                                @error('marca')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-2 form-group">
                                <label class="mb-1">Presión (PSI)</label>
                                <input type="number" step="0.1" min="0" wire:model="presion_recomendada"
                                    class="form-control form-control-sm @error('presion_recomendada') is-invalid @enderror">
                                @error('presion_recomendada')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-8 form-group mb-0">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input"
                                        id="rodado-activo-{{ $this->getId() }}" wire:model="activo">
                                    <label class="custom-control-label" for="rodado-activo-{{ $this->getId() }}">Activo</label>
                                </div>
                            </div>
                            <div class="col-md-4 form-group mb-0 text-right">
                                <button type="submit" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3) !important;">
                                    <i class="fas fa-save"></i>
                                    {{ $editandoId ? 'Guardar cambios' : 'Agregar' }}
                                </button>
                                @if ($editandoId)
                                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="resetForm">
                                        Cancelar
                                    </button>
                                @endif
                            </div>
                        </form>

                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Medida</th>
                                    <th>Posición</th>
                                    <th>Marca</th>
                                    <th>Presión</th>
                                    <th>Estado</th>
                                    <th class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    <tr wire:key="rodado-{{ $item->id }}">
                                        <td class="align-middle">{{ $item->nombre }}</td>
                                        <td class="align-middle">{{ $item->medida ?? '-' }}</td>
                                        <td class="align-middle">{{ $item->posicion_label }}</td>
                                        <td class="align-middle">{{ $item->marca ?? '-' }}</td>
                                        <td class="align-middle">{{ $item->presion_recomendada ? $item->presion_recomendada . ' PSI' : '-' }}</td>
                                        <td class="align-middle">
                                            @if ($item->activo)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-right align-middle">
                                            <button type="button" class="btn btn-outline-warning btn-xs"
                                                wire:click="editar({{ $item->id }})" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-xs"
                                                wire:click="eliminar({{ $item->id }})"
                                                wire:confirm="¿Eliminar este ítem del catálogo?" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">
                                            No hay tipos de rodado cargados todavía.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="cerrar">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>