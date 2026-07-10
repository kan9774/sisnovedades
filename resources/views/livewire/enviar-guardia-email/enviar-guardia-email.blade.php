<div class="d-inline">
    <button type="button" class="btn btn-outline-primary btn-ml ml-1" wire:click="abrir"
        data-toggle="tooltip" title="Enviar novedades por correo">
        <i class="fa-solid fa-envelopes-bulk"></i>
    </button>

    <div class="modal fade" id="modalEnviarGuardia" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enviar novedades por correo</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
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
                                            id="dest-{{ $usuario->id }}">
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
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" wire:click="enviar"
                        wire:loading.attr="disabled" wire:target="enviar">
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
</script>
@endscript