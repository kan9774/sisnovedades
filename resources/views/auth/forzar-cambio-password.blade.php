@extends('layouts.guest')

@section('title', 'Cambiar contraseña')
@section('header_title', 'SIS-Novedades')
@section('header_subtitle', 'Tenés que cambiar tu contraseña para continuar')

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

    <p class="text-muted mb-4" style="font-size: 0.9rem;">
        Tu cuenta se creó con una contraseña provisoria. Por seguridad, definí una nueva antes de seguir.
    </p>

    <form action="{{ route('password.forzar-cambio.update') }}" method="POST">
        @csrf

        <div class="form-group">
            <div class="input-group mb-3">
                <input type="password" name="password"
                    class="form-control guest-form-control @error('password') is-invalid @enderror"
                    placeholder="Nueva contraseña" autofocus>
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
            <i class="fas fa-key mr-2"></i> Guardar y continuar
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="#" class="guest-link"
            onclick="event.preventDefault(); document.getElementById('logout-form-forzar').submit();">
            <i class="fas fa-sign-out-alt mr-1"></i> Cerrar sesión
        </a>
        <form id="logout-form-forzar" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
@stop