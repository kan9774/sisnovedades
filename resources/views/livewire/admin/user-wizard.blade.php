<div class="card card-outline-ops">
    <div class="card-header-ops">
        <div class="card-header-ops__title-wrap">
            <h3 class="card-title-ops mb-0">Nuevo Usuario</h3>
            <span class="card-header-ops__eyebrow">Completá los pasos para registrar un usuario</span>
        </div>
    </div>
    <div class="card-body">

        {{-- Indicador de pasos: círculos numerados + línea conectora --}}
        <div class="wizard-steps">
            @php
                $pasos = [1 => 'Cédula', 2 => 'Grado / Unidad', 3 => 'Datos Personales'];
            @endphp
            @foreach ($pasos as $numero => $etiqueta)
                <div
                    class="wizard-step {{ $step === $numero ? 'is-active' : '' }} {{ $step > $numero ? 'is-done' : '' }}">
                    <div class="wizard-step__circle">
                        @if ($step > $numero)
                            <i class="fas fa-check"></i>
                        @else
                            {{ $numero }}
                        @endif
                    </div>
                    <div class="wizard-step__label">{{ $etiqueta }}</div>
                </div>
                @if ($numero < count($pasos))
                    <div class="wizard-step__line {{ $step > $numero ? 'is-done' : '' }}"></div>
                @endif
            @endforeach
        </div>

        <div class="wizard-panel">
            {{-- PASO 1: Cédula --}}
            @if ($step === 1)
                <h5 class="wizard-panel__title"> Ingrese el documento sin el dígito conprobador, el sistema lo comprueba automáticamente.</h5>                <form wire:submit="guardarPaso1">
                    <div class="form-group">
                        <label>Cédula</label>
                        <div class="input-group" style="max-width: 220px;">
                            <input type="text" wire:model.live="ci" maxlength="8"
                                class="form-control @error('ci') is-invalid @enderror" placeholder="1234567">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    {{ $digito_verificador !== null ? '-' . $digito_verificador : '- ?' }}
                                </span>
                            </div>
                            @error('ci')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <small class="text-muted">Se valida que no exista otro usuario con esta cédula.</small>
                    </div>

                    <div class="wizard-nav">
                        <a href="{{ route('admin.users.index') }}" class="btn wizard-btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-ops-primary" wire:loading.attr="disabled"
                            wire:target="guardarPaso1">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            @endif

            {{-- PASO 2: Grado / Unidad --}}
            @if ($step === 2)
                <h5 class="wizard-panel__title">Grado y Unidad de Destino</h5>
                <form wire:submit="guardarPaso2">
                    <div class="form-group">
                        <label>Tipo de ingreso</label>
                        <select wire:model.live="tipo" class="form-control">
                            <option value="alta">Alta (ingreso nuevo)</option>
                            <option value="pase">Pase (viene de otra unidad)</option>
                        </select>
                    </div>

                    @if ($tipo === 'alta')
                        <div class="form-group">
                            <label>Grado</label>
                            <input type="text" class="form-control"
                                value="{{ $this->grados->firstWhere('id', (int) $grado_id)?->nombre }}" disabled>
                            <small class="text-muted">Todo ingreso nuevo arranca en Sdo. 1°.</small>
                        </div>
                    @else
                        <div class="form-group">
                            <label>Grado</label>
                            <select wire:model="grado_id" class="form-control @error('grado_id') is-invalid @enderror">
                                @foreach ($this->grados as $grado)
                                    <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                                @endforeach
                            </select>
                            @error('grado_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Fecha {{ $tipo === 'alta' ? 'de ingreso' : 'en que se produce el pase' }}</label>
                        <input type="date" wire:model="fecha"
                            class="form-control @error('fecha') is-invalid @enderror">
                        @error('fecha')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                        @if ($tipo === 'pase')
                            <small class="text-muted">
                                La unidad queda vigente desde el 1° del mes siguiente a esta fecha.
                            </small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Unidad</label>
                        <select wire:model="unidad_id" class="form-control @error('unidad_id') is-invalid @enderror">
                            <option value="">-- Seleccionar --</option>
                            @foreach ($this->unidades as $unidad)
                                <option value="{{ $unidad->id }}">{{ $unidad->nombre }}</option>
                            @endforeach
                        </select>
                        @error('unidad_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="wizard-nav">
                        <button type="button" class="btn wizard-btn-secondary" wire:click="volverPaso(1)">
                            <i class="fas fa-arrow-left"></i> Atrás
                        </button>
                        <button type="submit" class="btn btn-ops-primary" wire:loading.attr="disabled"
                            wire:target="guardarPaso2">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            @endif

            {{-- PASO 3: Datos personales --}}
            @if ($step === 3)
                <h5 class="wizard-panel__title">Datos Personales</h5>
                <form wire:submit="guardarPaso3">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nombre</label>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Segundo Nombre</label>
                            <input type="text" wire:model="segundo_nombre" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Apellido</label>
                            <input type="text" wire:model="last_name"
                                class="form-control @error('last_name') is-invalid @enderror">
                            @error('last_name')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Segundo Apellido</label>
                            <input type="text" wire:model="segundo_apellido" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" wire:model="fecha_nacimiento"
                                class="form-control @error('fecha_nacimiento') is-invalid @enderror">
                            @error('fecha_nacimiento')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Email</label>
                            <input type="email" wire:model="email"
                                class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Contraseña</label>
                            <input type="password" wire:model="password"
                                class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Confirmar Contraseña</label>
                            <input type="password" wire:model="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Roles</label>
                        <div>
                            @foreach ($this->rolesDisponibles as $rol)
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" wire:model="roles" value="{{ $rol->id }}"
                                        class="form-check-input" id="rol{{ $rol->id }}">
                                    <label class="form-check-label" for="rol{{ $rol->id }}">
                                        {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="wizard-nav">
                        <button type="button" class="btn wizard-btn-secondary" wire:click="volverPaso(2)">
                            <i class="fas fa-arrow-left"></i> Atrás
                        </button>
                        <button type="submit" class="btn btn-ops-primary" wire:loading.attr="disabled"
                            wire:target="guardarPaso3">
                            <i class="fas fa-check"></i> Crear Usuario
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
