@extends('layouts.app')

@section('subtitle', 'Editar Usuario')
@section('content_header_title', 'Usuarios')
@section('content_header_subtitle', 'Editar')

@section('content_body')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Editar usuario</h3>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Grado</label>
                                <input type="text" name="grade"
                                    class="form-control @error('grade') is-invalid @enderror"
                                    value="{{ old('grade', $user->grade) }}" required>
                                @error('grade')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Apellido</label>
                                <input type="text" name="last_name"
                                    class="form-control @error('last_name') is-invalid @enderror"
                                    value="{{ old('last_name', $user->last_name) }}" required>
                                @error('last_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Rol</label>
                        <select name="rol_id" class="form-control @error('rol_id') is-invalid @enderror" required>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}"
                                    {{ old('rol_id', $user->rol_id) == $rol->id ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('rol_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    @if (auth()->user()->isAdmin())
                        <div class="form-group">
                            <label>
                                Permisos individuales
                                <small class="text-muted">(se suman a los del rol seleccionado)</small>
                            </label>
                            <div class="row">
                                @forelse($permisos as $permiso)
                                    <div class="col-md-4">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input"
                                                id="permiso_directo_{{ $permiso->id }}" name="permisos_directos[]"
                                                value="{{ $permiso->id }}"
                                                {{ in_array($permiso->id, old('permisos_directos', $user->permisosDirectos->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="permiso_directo_{{ $permiso->id }}">
                                                {{ ucfirst(str_replace('_', ' ', $permiso->name)) }}
                                                <br>
                                                <small class="text-muted">{{ $permiso->description }}</small>
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-muted mb-0">No hay permisos individuales cargados en el sistema.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->isSuperAdmin())
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="is_super_admin"
                                name="is_super_admin" value="1"
                                {{ old('is_super_admin', $user->is_super_admin) ? 'checked' : '' }}
                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            <label class="form-check-label" for="is_super_admin">
                                Este usuario es SuperAdmin
                            </label>
                            @if ($user->id === auth()->id())
                                <input type="hidden" name="is_super_admin" value="1">
                                <small class="text-muted d-block">No podés quitarte el rol de SuperAdmin a vos mismo.</small>
                            @endif
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nueva contraseña <small class="text-muted">(opcional)</small></label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirmar contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop