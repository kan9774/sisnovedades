<div class="col-lg-6">
    <div class="card shadow-sm border-0">
        <div class="card-body">

            @if ($enviado)
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle mr-1"></i>
                    ¡Mensaje enviado correctamente! Te responderemos a la brevedad.
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
                    <span wire:loading.remove wire:target="enviar">Enviar mensaje</span>
                    <span wire:loading wire:target="enviar">
                        <i class="fas fa-spinner fa-spin"></i> Enviando...
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>