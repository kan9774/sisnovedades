<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    <x-ops-card title="Editar Usuario: " icon="user-edit" :title-suffix="$user?->exists ? trim($user->grado->nombre ?? '') . '  ' . $user->name . ' ' . $user->last_name : null">
        <x-slot:header>
        </x-slot:header>
        <x-nav-tabs-ops :tabs="[
            'general' => 'General',
            'direccion' => 'Dirección',
            'roles' => 'Roles',
            'altas-bajas' => 'Altas/Bajas',
            'pases' => 'Pases',
            'comisiones' => 'Comisiones',
            'historial-grados' => 'Grados',
            'csm' => 'C.S.M.',
            'cc' => 'C.C.',
        ]" :active="$activeTab" />
        <form id="userForm" wire:submit="save">

            {{-- TAB: GENERAL --}}
            <div class="{{ $activeTab === 'general' ? '' : 'd-none' }}">
                @if ($user?->exists && !$this->puedeEditarDatosBasicos())
                    <div class="alert alert-warning py-2">
                        <i class="fas fa-lock"></i> Los datos personales de este usuario solo pueden ser
                        modificados por un administrador.
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cédula de Identidad</label>
                            <input type="text" wire:model.blur="ci" maxlength="8" placeholder="1234567"
                                class="form-control @error('ci') is-invalid @enderror" @disabled(!$this->puedeEditarDatosBasicos())>
                            @error('ci')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            @if ($user?->ci)
                                <small class="text-muted">Actual: {{ $user->ci_formateado }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Díg. Verif.</label>
                            <input type="text" value="{{ $digito_verificador !== null ? $digito_verificador : '' }}"
                                class="form-control text-center bg-light" readonly tabindex="-1">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" wire:model="fecha_nacimiento"
                                class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                @disabled(!$this->puedeEditarDatosBasicos())>
                            @error('fecha_nacimiento')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Grado</label>
                            <select wire:model="grado_id" class="form-control @error('grado_id') is-invalid @enderror"
                                @disabled(!$this->puedeEditarDatosBasicos()) disabled>
                                <option value="">Seleccionar...</option>
                                @foreach ($grados as $grado)
                                    <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                                @endforeach
                            </select>
                            @error('grado_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror" @disabled(!$this->puedeEditarDatosBasicos())>
                            @error('name')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Segundo Nombre</label>
                            <input type="text" wire:model="segundo_nombre"
                                class="form-control @error('segundo_nombre') is-invalid @enderror"
                                @disabled(!$this->puedeEditarDatosBasicos())>
                            @error('segundo_nombre')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Apellido</label>
                            <input type="text" wire:model="last_name"
                                class="form-control @error('last_name') is-invalid @enderror"
                                @disabled(!$this->puedeEditarDatosBasicos())>
                            @error('last_name')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Segundo Apellido</label>
                            <input type="text" wire:model="segundo_apellido"
                                class="form-control @error('segundo_apellido') is-invalid @enderror"
                                @disabled(!$this->puedeEditarDatosBasicos())>
                            @error('segundo_apellido')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Unidad de Destino</label>
                            <select wire:model="unidad_id"
                                class="form-control @error('unidad_id') is-invalid @enderror">
                                <option value="">-- Seleccionar --</option>
                                @foreach ($unidades as $unidad)
                                    <option value="{{ $unidad->id }}">{{ $unidad->nombre }}</option>
                                @endforeach
                            </select>
                            @error('unidad_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Oficina <small class="text-muted">(opcional)</small></label>
                            <select wire:model="oficina_id"
                                class="form-control @error('oficina_id') is-invalid @enderror">
                                <option value="">-- Ninguna --</option>
                                @foreach ($oficinas as $oficina)
                                    <option value="{{ $oficina->id }}">{{ $oficina->nombre }}</option>
                                @endforeach
                            </select>
                            @error('oficina_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: DIRECCIÓN --}}
            <div class="{{ $activeTab === 'direccion' ? '' : 'd-none' }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Departamento</label>
                            <select wire:model="departamento_id"
                                class="form-control @error('departamento_id') is-invalid @enderror">
                                <option value="">-- Seleccionar --</option>
                                @foreach ($departamentos as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                @endforeach
                            </select>
                            @error('departamento_id')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Localidad</label>
                            <input type="text" wire:model="localidad"
                                class="form-control @error('localidad') is-invalid @enderror">
                            @error('localidad')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Calle</label>
                            <input type="text" wire:model="calle"
                                class="form-control @error('calle') is-invalid @enderror">
                            @error('calle')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Número</label>
                            <input type="text" wire:model="numero" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Esquina</label>
                            <input type="text" wire:model="esquina" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Apartamento</label>
                            <input type="text" wire:model="apartamento" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Barrio</label>
                            <input type="text" wire:model="barrio" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Código Postal</label>
                            <input type="text" wire:model="codigo_postal" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Referencia <small class="text-muted">(ej: casa azul, portón negro)</small></label>
                    <input type="text" wire:model="referencia" class="form-control">
                </div>
            </div>

            {{-- TAB: ROLES --}}
            <div class="{{ $activeTab === 'roles' ? '' : 'd-none' }}">
                @if ($user?->exists && !$this->puedeEditarDatosBasicos())
                    <div class="alert alert-warning py-2">
                        <i class="fas fa-lock"></i> El acceso (email, contraseña) y los roles de este usuario
                        solo pueden ser modificados por un administrador.
                    </div>
                @endif

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" wire:model="email"
                        class="form-control @error('email') is-invalid @enderror" @disabled(!$this->puedeEditarDatosBasicos())>
                    @error('email')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ $user?->exists ? 'Nueva contraseña' : 'Contraseña' }}
                                @if ($user?->exists)
                                    <small class="text-muted">(opcional)</small>
                                @endif
                            </label>
                            <input type="password" wire:model="password"
                                class="form-control @error('password') is-invalid @enderror"
                                @disabled(!$this->puedeEditarDatosBasicos())>
                            @error('password')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Confirmar contraseña</label>
                            <input type="password" wire:model="password_confirmation" class="form-control"
                                @disabled(!$this->puedeEditarDatosBasicos())>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Roles</label>
                    <div class="row">
                        @foreach ($rolesDisponibles as $rol)
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="rol_{{ $rol->id }}"
                                        wire:model="roles" value="{{ $rol->id }}" @disabled(!$this->puedeEditarDatosBasicos())>
                                    <label class="custom-control-label" for="rol_{{ $rol->id }}">
                                        {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                                        @if ($rol->description)
                                            <br><small class="text-muted">{{ $rol->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('roles')
                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                @if (auth()->user()->isSuperAdmin())
                    <div class="form-group form-check">
                        <input type="checkbox" wire:model="is_super_admin" class="form-check-input"
                            id="is_super_admin" {{ $user?->id === auth()->id() ? 'disabled' : '' }}>
                        <label class="form-check-label" for="is_super_admin">Este usuario es
                            SuperAdmin</label>
                        @if ($user?->id === auth()->id())
                            <small class="text-muted d-block">No podés quitarte el rol de SuperAdmin a vos
                                mismo.</small>
                        @endif
                    </div>
                @endif
            </div>
        </form>
        {{-- TAB: ALTAS/BAJAS --}}
        <div class="{{ $activeTab === 'altas-bajas' ? '' : 'd-none' }}">
            <livewire:admin.historial-estado-panel :user="$user" />
        </div>

        {{-- TAB: PASES --}}
        <div class="{{ $activeTab === 'pases' ? '' : 'd-none' }}">
            <livewire:admin.pase-panel :user="$user" />
        </div>

        {{-- TAB: COMISIONES --}}
        <div class="{{ $activeTab === 'comisiones' ? '' : 'd-none' }}">
            <livewire:admin.comision-panel :user="$user" />
        </div>

        {{-- TAB: HISTORIAL DE GRADOS --}}
        <div class="{{ $activeTab === 'historial-grados' ? '' : 'd-none' }}">
            <livewire:admin.historial-grados-panel :user="$user" />
        </div>

        {{-- TAB: C.S.M. --}}
        <div class="{{ $activeTab === 'csm' ? '' : 'd-none' }}">
            @if ($user?->exists)
                <livewire:admin.csm-panel :user="$user" />
            @else
                <p class="text-muted">
                    Guardá el usuario primero para poder generar contratos C.S.M.
                </p>
            @endif
        </div>

        {{-- TAB: C.C. (Credencial Cívica + Jefe de Unidad) --}}
        <div class="{{ $activeTab === 'cc' ? '' : 'd-none' }}">
            @if ($user?->exists)
                <livewire:admin.credencial-civica-panel :user="$user" />
                <hr class="my-3">
                <livewire:admin.jefes-unidad-panel />
            @else
                <p class="text-muted">
                    Guardá el usuario primero para poder cargar la credencial cívica.
                </p>
            @endif
        </div>
        <x-slot:footer>
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.users.index') }}" class="btn-ops btn-ops-secondary footer-btn"
                    style="padding-left: 1rem !important;">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" form="userForm" class="btn-ops btn-ops-primary" wire:loading.attr="disabled"
                    style="padding-right: 1rem !important;">
                    <span wire:loading.remove><i class="fas fa-save"></i> Guardar</span>
                    <span wire:loading>Guardando...</span>
                </button>
            </div>
        </x-slot:footer>
    </x-ops-card>
</div>