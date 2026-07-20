<div class="col-lg-6">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-lightbulb mr-2"></i>
                Envíanos tu sugerencia
            </h5>
        </div>
        <div class="card-body">
            @if ($enviado)
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle mr-1"></i>
                    ¡Gracias por tu sugerencia! La analizaremos y te responderemos a la brevedad.
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
                        id="nombre" placeholder="Tu nombre" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror"
                        id="email" placeholder="tu@email.com" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="rol">Rol en la organización</label>
                    <select wire:model="rol" class="form-select @error('rol') is-invalid @enderror" id="rol" required>
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
                    @error('rol')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="prioridad">Prioridad</label>
                    <select wire:model="prioridad" class="form-select @error('prioridad') is-invalid @enderror" id="prioridad" required>
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                    @error('prioridad')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tipo">Tipo de sugerencia</label>
                    <select wire:model="tipo" class="form-select @error('tipo') is-invalid @enderror" id="tipo" required>
                        <option value="mejora">Mejora de funcionalidad</option>
                        <option value="bug">Reporte de error</option>
                        <option value="nueva_funcionalidad">Nueva funcionalidad</option>
                        <option value="diseño">Mejora de diseño/UX</option>
                        <option value="seguridad">Seguridad</option>
                        <option value="otro">Otro</option>
                    </select>
                    @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="mensaje">Descripción detallada</label>
                    <textarea wire:model="mensaje" class="form-control @error('mensaje') is-invalid @enderror"
                        id="mensaje" rows="5" placeholder="Describe tu sugerencia en detalle..." required></textarea>
                    @error('mensaje')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="adjunto">Archivo adjunto (opcional)</label>
                    <input type="file" wire:model="adjunto" class="form-control @error('adjunto') is-invalid @enderror"
                        id="adjunto" accept=".txt,.pdf,.doc,.docx,.jpg,.png,.gif">
                    <div class="form-text">Tamaño máximo: 5MB</div>
                    @error('adjunto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check">
                    <input type="checkbox" wire:model="aceptar" class="form-check-input" id="aceptar" required>
                    <label class="form-check-label" for="aceptar">
                        Acepto que mis datos sean usados para mejorar el sistema
                    </label>
                    @error('aceptar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" wire:loading.attr="disabled"
                    wire:target="enviar">
                    <span wire:loading.remove wire:target="enviar">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar sugerencia
                    </span>
                    <span wire:loading wire:target="enviar">
                        <i class="fas fa-spinner fa-spin"></i>
                        Enviando...
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>
