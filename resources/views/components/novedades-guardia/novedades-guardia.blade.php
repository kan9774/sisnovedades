<div>
    @if ($guardia->status === 'open' && $puedeOperarGuardia)
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-outline-info btn-sm" wire:click="abrirCrear">
                <i class="fas fa-plus-circle"></i> Registrar Tráfico
            </button>
        </div>
    @endif

    <table class="table table-striped table-hover mb-0" style="width: 100%">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Hora</th>
                <th>Tipo</th>
                <th>Dirección</th>
                <th>Número</th>
                <th>Asunto</th>
                <th>Clasificación</th>
                <th>Escribiente</th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->novedades as $novedad)
                <tr wire:key="novedad-{{ $novedad->id }}">
                    <td>{{ $loop->iteration + ($this->novedades->currentPage() - 1) * $this->novedades->perPage() }}
                    </td>
                    <td>{{ $novedad->time?->format('H:i') }}</td>
                    <td>{{ $novedad->type }}</td>
                    <td>
                        @if ($novedad->direction === 'Recibido')
                            <span class="badge badge-success">Recibido</span>
                        @else
                            <span class="badge badge-warning">Expedido</span>
                        @endif
                    </td>
                    <td>{{ $novedad->number }}</td>
                    <td>{{ Str::limit($novedad->affair, 40) }}</td>
                    <td>
                        @php
                            $colores = [
                                'Rutinario' => 'secondary',
                                'Prioritario' => 'primary',
                                'Urgente' => 'warning',
                                'Destello' => 'danger',
                            ];
                        @endphp
                        <span class="badge badge-{{ $colores[$novedad->clasification] ?? 'secondary' }}">
                            {{ $novedad->clasification }}
                        </span>
                    </td>
                    <td>{{ $novedad->escribiente->name ?? '-' }}</td>
                    <td>
                        <livewire:estado-novedad :novedad="$novedad" :guardia="$guardia" :compacto="true"
                            :key="'estado-novedad-tabla-' . $novedad->id" />
                    </td>
                    <td class="text-center align-middle">
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('admin.guardias.novedades.show', [$guardia, $novedad]) }}"
                                class="btn btn-outline-info btn-xs mr-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if ($guardia->status === 'open' && $puedeOperarGuardia)
                                <button type="button" wire:click="abrirEditar({{ $novedad->id }})"
                                    class="btn btn-outline-warning btn-xs mr-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" wire:click="eliminar({{ $novedad->id }})"
                                    wire:confirm="¿Eliminar esta novedad?" class="btn btn-outline-danger btn-xs">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">
                        <div class="text-center text-muted py-4">
                            No hay tráficos registrados en esta guardia.
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($this->novedades->hasPages())
        <div class="mt-3">{{ $this->novedades->links() }}</div>
    @endif

    {{-- Modal --}}
    <div class="modal fade" id="modalNovedad" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="guardar">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editandoId ? 'Editar Novedad' : 'Registrar Novedad' }}</h5>
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

                        @if (!$editandoId)
                            <div class="form-group">
                                <label>
                                    Adjunto
                                    <small class="text-muted">(opcional, max: 10MB)</small>
                                </label>
                                <input type="file" wire:model="archivo"
                                    class="form-control @error('archivo') is-invalid @enderror"
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <div wire:loading wire:target="archivo" class="text-muted small mt-1">
                                    <i class="fas fa-spinner fa-spin"></i> Subiendo archivo...
                                </div>
                                @error('archivo')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        @else
                            <small class="text-muted d-block">
                                <i class="fas fa-paperclip"></i> Los adjuntos se gestionan desde el detalle de la
                                novedad.
                            </small>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                            wire:target="guardar">
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
        $wire.on('abrir-modal-novedad', () => $('#modalNovedad').modal('show'));
        $wire.on('cerrar-modal-novedad', () => $('#modalNovedad').modal('hide'));
    </script>
@endscript