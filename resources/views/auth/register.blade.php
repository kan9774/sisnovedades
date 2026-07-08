@extends('layouts.guest')

@section('title', 'Registrarse')
@section('header_title', 'SIS-Novedades')
@section('header_subtitle', 'Registrarse como visitante')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger guest-alert alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register') }}" method="POST">
        @csrf

        <div class="form-group">
            <div class="input-group mb-3">
                <input type="text" name="name"
                    class="form-control guest-form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                    placeholder="Nombre" autofocus>
                <div class="input-group-append">
                    <span class="input-group-text guest-input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <div class="input-group mb-3">
                <input type="text" name="last_name"
                    class="form-control guest-form-control @error('last_name') is-invalid @enderror"
                    value="{{ old('last_name') }}" placeholder="Apellido">
                <div class="input-group-append">
                    <span class="input-group-text guest-input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
                @error('last_name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <div class="input-group mb-3">
                <input type="text" name="grade"
                    class="form-control guest-form-control @error('grade') is-invalid @enderror" value="{{ old('grade') }}"
                    placeholder="Grado (opcional)">
                <div class="input-group-append">
                    <span class="input-group-text guest-input-group-text">
                        <i class="fas fa-user-shield"></i>
                    </span>
                </div>
                @error('grade')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group">
            <div class="input-group mb-3">
                <select name="unidad_id" class="form-control guest-form-control @error('unidad_id') is-invalid @enderror">
                    <option value="">-- Seleccionar Unidad --</option>
                    @foreach ($unidades as $unidad)
                        <option value="{{ $unidad->id }}" {{ old('unidad_id') == $unidad->id ? 'selected' : '' }}>
                            {{ $unidad->nombre }}
                        </option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <span class="input-group-text guest-input-group-text">
                        <i class="fas fa-building"></i>
                    </span>
                </div>
                @error('unidad_id')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
        <div class="form-group">
            <div class="input-group mb-3">
                <input type="email" name="email"
                    class="form-control guest-form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="Email">
                <div class="input-group-append">
                    <span class="input-group-text guest-input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                </div>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <div class="input-group mb-3">
                <input type="password" name="password"
                    class="form-control guest-form-control @error('password') is-invalid @enderror"
                    placeholder="Contraseña">
                <div class="input-group-append">
                    <span class="input-group-text guest-input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <div class="input-group mb-3">
                <input type="password" name="password_confirmation" class="form-control guest-form-control"
                    placeholder="Confirmar contraseña">
                <div class="input-group-append">
                    <span class="input-group-text guest-input-group-text">
                        <i class="fas fa-check-circle"></i>
                    </span>
                </div>
            </div>
        </div>

        <button type="submit" class="guest-btn-primary">
            <i class="fas fa-user-plus mr-2"></i> Registrarse
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="guest-link">
            <i class="fas fa-arrow-left mr-1"></i> ¿Ya tenés cuenta? Iniciá sesión
        </a>
    </div>
@stop

@section('footer')
    <a href="/" class="guest-link-muted">
        <i class="fas fa-home mr-1"></i> Volver al inicio
    </a>
@stop
