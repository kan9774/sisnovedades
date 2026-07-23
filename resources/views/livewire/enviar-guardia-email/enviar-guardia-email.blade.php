<div class="d-inline">
    <button type="button" class="btn btn-primary btn-ml ml-1" wire:click="abrir" data-toggle="tooltip"
        title="Enviar novedades por correo"
        style="background: linear-gradient(135deg, #0B2545 0%, #0F3460 100%); box-shadow: 0 2px 8px rgba(11, 37, 69, 0.35); border: none;">
        <i class="fa-solid fa-envelopes-bulk"></i>
    </button>

    {{-- Panel pantalla completa --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalEnviarGuardia" wire:ignore.self>
        <div class="ops-panel">
            <div class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Envío de Novedades</span>
                        <h5 class="ops-panel__title">Enviar novedades por correo</h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalEnviarGuardia')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body" wire:loading.class="opacity-50" wire:target="enviar">
                    <div class="ops-panel__content">
                        @if ($mensajeExito)
                            <div class="alert {{ $fallidosCount > 0 ? 'alert-warning' : 'alert-success' }} py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span>{{ $mensajeExito }}</span>
                                @if ($fallidosCount > 0)
                                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="irACorreosFallidos()">
                                        <i class="fas fa-envelope-circle-check"></i> Ver correos fallidos
                                    </button>
                                @endif
                            </div>
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
                                <a class="nav-link {{ $modoSeleccion === 'manual' ? 'active' : '' }}" href="#"
                                    wire:click.prevent="$set('modoSeleccion', 'manual')">
                                    <i class="fas fa-user-check"></i> Elegir uno por uno
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $modoSeleccion === 'grupo' ? 'active' : '' }}" href="#"
                                    wire:click.prevent="$set('modoSeleccion', 'grupo')">
                                    <i class="fas fa-users"></i> Usar un grupo guardado
                                </a>
                            </li>
                        </ul>

                        @if ($modoSeleccion === 'manual')
                            <div style="max-height: 320px; overflow-y: auto;">
                                @forelse ($this->usuariosPorOficina as $oficinaNombre => $usuarios)
                                    <div class="mb-3">
                                        <strong class="d-block small text-uppercase text-muted mb-1">
                                            {{ $oficinaNombre }}
                                        </strong>
                                        @foreach ($usuarios as $usuario)
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" wire:model="destinatarios"
                                                    value="{{ $usuario->id }}" id="dest-{{ $usuario->id }}"
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
                                <select class="form-control" wire:model="grupoSeleccionado" wire:loading.attr="disabled"
                                    wire:target="enviar">
                                    <option value="">Seleccioná un grupo...</option>
                                    @foreach ($this->grupos as $grupo)
                                        <option value="{{ $grupo->id }}">
                                            {{ $grupo->nombre }} ({{ $grupo->usuarios_count }}
                                            usuario{{ $grupo->usuarios_count === 1 ? '' : 's' }})
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
                                wire:model.live="incluirAdjuntos" @disabled($enviarZip)>
                            <label class="form-check-label" for="incluirAdjuntos">
                                Incluir adjuntos (embebidos en el PDF)
                            </label>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="enviarZip"
                                wire:model.live="enviarZip" @disabled($incluirAdjuntos)>
                            <label class="form-check-label" for="enviarZip">
                                Enviar como ZIP (PDF + adjuntos sueltos, más liviano)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalEnviarGuardia')"
                        wire:loading.attr="disabled" wire:target="enviar">
                        Cancelar
                    </button>
                    <button type="button" class="btn" wire:click="enviar" wire:loading.attr="disabled"
                        wire:target="enviar"
                        style="background: linear-gradient(135deg, #FFD200 0%, #FBCB5B 100%) !important; color: #0B2545 !important; font-weight: 700; box-shadow: 0 2px 8px rgba(255, 210, 0, 0.35) !important; border: none;">
                        <span wire:loading.remove wire:target="enviar"><i class="fas fa-paper-plane"></i>
                            Enviar</span>
                        <span wire:loading wire:target="enviar"><i class="fas fa-spinner fa-spin"></i>
                            Enviando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>

<style>
    .ops-panel-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1060;
        background: #f4f5f7;
    }

    .ops-panel-overlay.is-open {
        display: block;
        animation: opsPanelFadeIn .16s ease-out;
    }

    .ops-panel {
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
    }

    .ops-panel__form {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .ops-panel__header {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.75rem;
        background: linear-gradient(135deg, #0B2545 0%, #0F3460 100%);
        border-bottom: 4px solid #FFD200;
    }

    .ops-panel__eyebrow {
        display: block;
        color: #FFD200;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .ops-panel__title {
        color: #fff;
        margin: 0;
        font-weight: 600;
    }

    .ops-panel__close {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
        border-radius: 6px;
        width: 38px;
        height: 38px;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s, border-color .15s;
    }

    .ops-panel__close:hover {
        background: rgba(255, 210, 0, 0.18);
        border-color: #FFD200;
        color: #FFD200;
    }

    .ops-panel__body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 2rem 1.75rem;
    }

    .ops-panel__content {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        border-radius: 10px;
        padding: 1.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .ops-panel__footer {
        flex: 0 0 auto;
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        padding: 1rem 1.75rem;
        background: #fff;
        border-top: 1px solid #e5e7eb;
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.04);
    }

    @keyframes opsPanelFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    body.ops-panel-open {
        overflow: hidden;
    }
</style>

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

        // Cierra este panel, cambia a la pestaña "Correos fallidos" de la
        // guardia (ya vive en show.blade.php, junto a este componente) y
        // actualiza el hash para que quede persistida al recargar.
        if (!window.irACorreosFallidos) {
            window.irACorreosFallidos = function () {
                cerrarOpsPanel('modalEnviarGuardia');
                const tabLink = document.querySelector('a[href="#tab-correos-fallidos"]');
                if (tabLink) {
                    $(tabLink).tab('show');
                    history.replaceState(null, null, '#tab-correos-fallidos');
                }
            };
        }

        $wire.on('abrir-modal-enviar-guardia', () => {
            document.getElementById('modalEnviarGuardia').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        // El backend hace: $this->dispatch('novedades-enviadas', fallidos: $fallidos);
        // al final del método enviar(). Si no hubo fallidos, el panel se
        // cierra solo; si hubo, se deja abierto para que el botón
        // "Ver correos fallidos" del mensaje siga visible.
        $wire.on('novedades-enviadas', (event) => {
            const fallidos = event?.fallidos ?? 0;
            if (fallidos > 0) {
                return;
            }
            setTimeout(() => {
                cerrarOpsPanel('modalEnviarGuardia');
            }, 1200);
        });
    </script>
@endscript