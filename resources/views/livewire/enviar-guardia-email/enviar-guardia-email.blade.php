<div class="d-inline">
    <button type="button" class="btn btn-primary btn-ml ml-1" wire:click="abrir"
        data-toggle="tooltip" title="Enviar novedades por correo" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3); border: none;">
        <i class="fa-solid fa-envelopes-bulk"></i>
    </button>

    <div class="modal fade" id="modalEnviarGuardia" tabindex="-1" wire:ignore.self style="background: rgba(255, 255, 255, 0.15) !important; backdrop-filter: blur(12px) saturate(180%) !important; -webkit-backdrop-filter: blur(12px) saturate(180%) !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; border-radius: 16px !important; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
                <div class="modal-header" style="background-color: #eef2ff !important;">
                    <h5 class="modal-title">Enviar novedades por correo</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" wire:loading.class="opacity-50" wire:target="enviar">
                    @if ($mensajeExito)
                        <div class="alert alert-success py-2">{{ $mensajeExito }}</div>
                    @endif

                    @error('destinatarios')
                        <div class="alert alert-danger py-2">{{ $message }}</div>
                    @enderror

                    <p class="text-muted small">
                        Se enviará el PDF de la guardia del {{ $guardia->date->format('d/m/Y') }}
                        a los destinatarios que elijas a continuación.
                    </p>

                    {{-- Selector de modo --}}
                    <ul class="nav nav-pills nav-sm mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $modoSeleccion === 'manual' ? 'active' : '' }}"
                               href="#" wire:click.prevent="$set('modoSeleccion', 'manual')">
                                <i class="fas fa-user-check"></i> Elegir uno por uno
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $modoSeleccion === 'grupo' ? 'active' : '' }}"
                               href="#" wire:click.prevent="$set('modoSeleccion', 'grupo')">
                                <i class="fas fa-users"></i> Usar un grupo guardado
                            </a>
                        </li>
                    </ul>

                    @if ($modoSeleccion === 'manual')
                        <div style="max-height: 260px; overflow-y: auto;">
                            @forelse ($this->usuariosPorOficina as $oficinaNombre => $usuarios)
                                <div class="mb-3">
                                    <strong class="d-block small text-uppercase text-muted mb-1">
                                        {{ $oficinaNombre }}
                                    </strong>
                                    @foreach ($usuarios as $usuario)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                wire:model="destinatarios" value="{{ $usuario->id }}"
                                                id="dest-{{ $usuario->id }}"
                                                wire:loading.attr="disabled" wire:target="enviar">
                                            <label class="form-check-label" for="dest-{{ $usuario->id }}">
                                                {{ $usuario->grade }} {{ $usuario->name }} {{ $usuario->last_name }}
                                                <span class="text-muted">({{ $usuario->email }})</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @empty
                                <p class="text-muted text-center py-3">No hay usuarios disponibles.</p>
                            @endforelse
                        </div>
                    @else
                        <div class="form-group">
                            <label>Grupo de destinatarios</label>
                            <select class="form-control" wire:model="grupoSeleccionado"
                                wire:loading.attr="disabled" wire:target="enviar">
                                <option value="">Seleccioná un grupo...</option>
                                @foreach ($this->grupos as $grupo)
                                    <option value="{{ $grupo->id }}">
                                        {{ $grupo->nombre }} ({{ $grupo->usuarios_count }} usuario{{ $grupo->usuarios_count === 1 ? '' : 's' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('grupoSeleccionado')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                            @if ($this->grupos->isEmpty())
                                <small class="text-muted d-block mt-2">
                                    No tenés grupos guardados todavía. Podés crearlos desde "Destinatarios PDF".
                                </small>
                            @endif
                        </div>
                    @endif

                    <hr class="my-3">

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="incluirAdjuntos"
                            wire:model="incluirAdjuntos" wire:loading.attr="disabled" wire:target="enviar">
                        <label class="form-check-label" for="incluirAdjuntos">
                            <i class="fas fa-paperclip"></i> Incluir documentación recibida (adjuntos)
                        </label>
                        <small class="text-muted d-block">
                            Agrega al PDF las imágenes y documentos recibidos durante la guardia.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"
                        wire:loading.attr="disabled" wire:target="enviar">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="enviar" wire:loading.attr="disabled" wire:target="enviar" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3) !important;">
                        <span wire:loading.remove wire:target="enviar"><i class="fas fa-paper-plane"></i> Enviar</span>
                        <span wire:loading wire:target="enviar"><i class="fas fa-spinner fa-spin"></i> Enviando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('abrir-modal-enviar-guardia', () => $('#modalEnviarGuardia').modal('show'));

    // El backend debe hacer: $this->dispatch('novedades-enviadas');
    // al final del método enviar() cuando el envío fue exitoso.
    $wire.on('novedades-enviadas', () => {
        setTimeout(() => {
            $('#modalEnviarGuardia').modal('hide');
        }, 1200);
    });
</script>
@endscript