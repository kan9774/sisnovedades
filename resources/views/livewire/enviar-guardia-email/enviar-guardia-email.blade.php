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
                        a los usuarios que selecciones a continuación.
                    </p>

                    <div style="max-height: 320px; overflow-y: auto;">
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