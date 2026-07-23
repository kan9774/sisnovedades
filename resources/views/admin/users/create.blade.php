@extends('layouts.app')

@section('subtitle', 'Nuevo Usuario')
@section('content_header_title', 'Usuarios')
@section('content_header_subtitle', 'Nuevo')

@section('content_body')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Crear usuario</h3>
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

                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Grado</label>
                                <input type="text" name="grade"
                                    class="form-control @error('grade') is-invalid @enderror" value="{{ old('grade') }}"
                                    required>
                                @error('grade')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nombre</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    required>
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
                                    value="{{ old('last_name') }}" required>
                                @error('last_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Roles</label>
                        <div class="row">
                            @foreach ($roles as $rol)
                                <div class="col-md-4">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="rol_{{ $rol->id }}"
                                            name="roles[]" value="{{ $rol->id }}"
                                            {{ in_array($rol->id, old('roles', [])) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="rol_{{ $rol->id }}">
                                            {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Unidad de Destino</label>
                        <select name="unidad_id" class="form-control @error('unidad_id') is-invalid @enderror" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach ($unidades as $unidad)
                                <option value="{{ $unidad->id }}"
                                    {{ old('unidad_id') == $unidad->id ? 'selected' : '' }}>
                                    {{ $unidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('unidad_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Oficina <small class="text-muted">(opcional — solo si el usuario pertenece a una
                                oficina)</small></label>
                        <select name="oficina_id" class="form-control @error('oficina_id') is-invalid @enderror">
                            <option value="">-- Ninguna --</option>
                            @foreach ($oficinas as $oficina)
                                <option value="{{ $oficina->id }}"
                                    {{ old('oficina_id') == $oficina->id ? 'selected' : '' }}>
                                    {{ $oficina->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('oficina_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contraseña</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirmar contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    @if (auth()->user()->isSuperAdmin())
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="is_super_admin" name="is_super_admin"
                                value="1" {{ old('is_super_admin') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_super_admin">
                                Este usuario es SuperAdmin
                            </label>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
