<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">

                @if ($enviado)
                    <div class="alert alert-success" role="alert" x-data
                        x-init="setTimeout(() => $wire.enviado = false, 4000)">
                        <i class="fas fa-check-circle mr-1"></i>
                        Transmisión recibida. Te responderemos a la brevedad.
                    </div>
                @endif

                @if ($error)
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $error }}
                    </div>
                @endif

                <form wire:submit="enviar">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror"
                            id="nombre" placeholder="Tu nombre">
                        @error('nombre')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" placeholder="tu@email.com">
                        @error('email')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="mensaje">Mensaje</label>
                        <textarea wire:model="mensaje" class="form-control @error('mensaje') is-invalid @enderror"
                            id="mensaje" rows="3" placeholder="Consulta..."></textarea>
                        @error('mensaje')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled"
                        wire:target="enviar">
                        <span wire:loading.remove wire:target="enviar"><i class="fas fa-paper-plane mr-2"></i>Enviar mensaje</span>
                        <span wire:loading wire:target="enviar">
                            <i class="fas fa-spinner fa-spin"></i> Enviando...
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <ul class="list-unstyled">
            <li><i class="fas fa-map-marker-alt"></i> Cuartel Peñarol, Ciudad de Montevideo, Uruguay</li>
            <li><i class="fas fa-phone"></i> +598 2 358-83-04</li>
            <li><i class="fas fa-envelope"></i> bcom1@ejercito.mil.uy</li>
            <li><i class="fas fa-clock"></i> Lunes a Viernes 08:00 - 17:00</li>
        </ul>
    </div>

    <div class="col-lg-6 mt-4 mt-lg-0">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Tu sugerencia nos ayuda a crecer
                </h5>
            </div>
            <div class="card-body">
                @if ($sugerencia_enviada)
                    <div class="alert alert-success" role="alert" x-data
                        x-init="setTimeout(() => $wire.sugerencia_enviada = false, 4000)">
                        <i class="fas fa-check-circle mr-1"></i>
                        ¡Gracias por tu sugerencia! La analizaremos y te responderemos a la brevedad.
                    </div>
                @endif

                @if ($sugerencia_error)
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $sugerencia_error }}
                    </div>
                @endif

                <form wire:submit="enviarSugerencia">
                    <div class="form-group">
                        <label for="sugerencia_nombre">Nombre</label>
                        <input type="text" wire:model="sugerencia_nombre" class="form-control @error('sugerencia_nombre') is-invalid @enderror"
                            id="sugerencia_nombre" placeholder="Tu nombre" required>
                        @error('sugerencia_nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sugerencia_email">Email</label>
                        <input type="email" wire:model="sugerencia_email" class="form-control @error('sugerencia_email') is-invalid @enderror"
                            id="sugerencia_email" placeholder="tu@email.com" required>
                        @error('sugerencia_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sugerencia_rol">Rol en la organización</label>
                        <select wire:model="sugerencia_rol" class="form-select @error('sugerencia_rol') is-invalid @enderror" id="sugerencia_rol" required>
                            <option value="">Selecciona una opción</option>
                            <option value="usuario_novedades">Usuario de Novedades</option>
                            <option value="usuario_palomar">Usuario de Palomar</option>
                            <option value="usuario_vehiculos">Usuario de Vehículos</option>
                            <option value="usuario_conductores">Usuario de Conductores</option>
                            <option value="usuario_guardia">Usuario de Guardia de Correos</option>
                            <option value="usuario_documentacion">Usuario de Documentación</option>
                            <option value="administrador">Administrador</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('sugerencia_rol')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sugerencia_prioridad">Prioridad</label>
                        <select wire:model="sugerencia_prioridad" class="form-select @error('sugerencia_prioridad') is-invalid @enderror" id="sugerencia_prioridad" required>
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                        @error('sugerencia_prioridad')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sugerencia_tipo">Tipo de sugerencia</label>
                        <select wire:model="sugerencia_tipo" class="form-select @error('sugerencia_tipo') is-invalid @enderror" id="sugerencia_tipo" required>
                            <option value="mejora">Mejora de funcionalidad</option>
                            <option value="bug">Reporte de error</option>
                            <option value="nueva_funcionalidad">Nueva funcionalidad</option>
                            <option value="diseño">Mejora de diseño/UX</option>
                            <option value="seguridad">Seguridad</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('sugerencia_tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sugerencia_mensaje">Descripción detallada</label>
                        <textarea wire:model="sugerencia_mensaje" class="form-control @error('sugerencia_mensaje') is-invalid @enderror"
                            id="sugerencia_mensaje" rows="5" placeholder="Describe tu sugerencia en detalle..." required></textarea>
                        @error('sugerencia_mensaje')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sugerencia_adjunto">Archivo adjunto (opcional)</label>
                        <input type="file" wire:model="sugerencia_adjunto" class="form-control @error('sugerencia_adjunto') is-invalid @enderror"
                            id="sugerencia_adjunto" accept=".txt,.pdf,.doc,.docx,.jpg,.png,.gif">
                        <div class="form-text">Tamaño máximo: 5MB</div>
                        @error('sugerencia_adjunto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check">
                        <input type="checkbox" wire:model="sugerencia_aceptar" class="form-check-input" id="sugerencia_aceptar" required>
                        <label class="form-check-label" for="sugerencia_aceptar">
                            Acepto que mis datos sean usados para mejorar el sistema
                        </label>
                        @error('sugerencia_aceptar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" wire:loading.attr="disabled"
                        wire:target="enviarSugerencia">
                        <span wire:loading.remove wire:target="enviarSugerencia">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Enviar sugerencia
                        </span>
                        <span wire:loading wire:target="enviarSugerencia">
                            <i class="fas fa-spinner fa-spin"></i>
                            Enviando...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>