<div class="d-inline">
    @can('update', $novedad)
        <button type="button" wire:click="abrir" class="btn btn-warning btn-sm mr-1"
            style="background-color: rgba(255, 193, 7, 0.15); border-color: rgba(255, 193, 7, 0.3); box-shadow: 0 2px 4px rgba(255, 193, 7, 0.2);" aria-label="Editar novedad">
            <i class="fas fa-edit"></i> Editar
        </button>
    @endcan

    <div class="modal fade" id="modalEditarNovedad-{{ $novedad->id }}" tabindex="-1" wire:ignore.self style="background: rgba(255, 255, 255, 0.15) !important; backdrop-filter: blur(12px) saturate(180%) !important; -webkit-backdrop-filter: blur(12px) saturate(180%) !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; border-radius: 16px !important; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="backdrop-filter: blur(10px);">
                <form wire:submit="guardar" style="--bs-form-control-focus-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);">
                    <div class="modal-header" style="background-color: #eef2ff !important;">
                        <h5 class="modal-title">Editar Novedad #{{ $novedad->id }}</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo <span class="text-danger">*</span></label>
                                    <select wire:model="type" class="form-control @error('type') is-invalid @enderror">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="Radio">Radio</option>
                                        <option value="Fax">Fax</option>
                                        <option value="Correo Electrónico">Correo Electrónico</option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Dirección <span class="text-danger">*</span></label>
                                    <select wire:model.live="direction"
                                        class="form-control @error('direction') is-invalid @enderror">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="Recibido">Recibido</option>
                                        <option value="Expedido">Expedido</option>
                                    </select>
                                    @error('direction')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            @if ($direction === 'Expedido')
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Destino</label>
                                        <input type="text" wire:model="destino"
                                            class="form-control @error('destino') is-invalid @enderror"
                                            placeholder="Ej: Cte.Rva.Gral.E.">
                                        @error('destino')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @elseif ($direction === 'Recibido')
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>¿Quién expide?</label>
                                        <select wire:model="organismo_id" class="form-control">
                                            <option value="">-- Seleccionar --</option>
                                            @foreach ($this->organismos as $organismo)
                                                <option value="{{ $organismo->id }}">{{ $organismo->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1">O escribí uno nuevo:</small>
                                        <input type="text" wire:model="organismo_nuevo" class="form-control mt-1"
                                            placeholder="Nuevo organismo...">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Número <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="number"
                                        class="form-control @error('number') is-invalid @enderror">
                                    @error('number')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hora <span class="text-danger">*</span></label>
                                    <input type="time" wire:model="time"
                                        class="form-control @error('time') is-invalid @enderror">
                                    @error('time')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Oficina <span class="text-danger">*</span></label>
                                    <select wire:model="office_id"
                                        class="form-control @error('office_id') is-invalid @enderror">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach ($this->oficinas as $oficina)
                                            <option value="{{ $oficina->id }}">{{ $oficina->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('office_id')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Clasificación <span class="text-danger">*</span></label>
                                    <select wire:model="clasification"
                                        class="form-control @error('clasification') is-invalid @enderror">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="Rutinario">Rutinario</option>
                                        <option value="Prioritario">Prioritario</option>
                                        <option value="Urgente">Urgente</option>
                                        <option value="Destello">Destello</option>
                                    </select>
                                    @error('clasification')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Asunto <small class="text-muted">(opcional)</small></label>
                                    <input type="text" wire:model="affair"
                                        class="form-control @error('affair') is-invalid @enderror">
                                    @error('affair')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Texto <span class="text-danger">*</span></label>
                            <textarea wire:model="text" rows="5" class="form-control @error('text') is-invalid @enderror"></textarea>
                            @error('text')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Adjunto</label>
                            <livewire:gestion-adjuntos :novedad="$novedad" :guardia="$guardia"
                                :key="'adjuntos-editar-modal-' . $novedad->id" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="box-shadow: 0 2px 4px rgba(108, 117, 125, 0.2);">Cancelar</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="guardar" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3) !important;">
                            <span wire:loading.remove wire:target="guardar"><i class="fas fa-save"></i> Guardar</span>
                            <span wire:loading wire:target="guardar"><i class="fas fa-spinner fa-spin"></i>
                                Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('abrir-modal-editar-novedad', () => $('#modalEditarNovedad-{{ $novedad->id }}').modal('show'));
    </script>
@endscript