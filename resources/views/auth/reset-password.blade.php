@extends('layouts.guest')

@section('title', 'Restablecer contraseña')
@section('header_title', 'SIS-Novedades')
@section('header_subtitle', 'Restablecer contraseña')

@section('content')
    @if($errors->any())
        <div class="alert alert-danger guest-alert alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('password.update') }}" method="POST">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group">
            <div class="input-group mb-3">
                <input type="email" name="email"
                       class="form-control guest-form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $request->email) }}"
                       placeholder="Email"
                       autofocus>
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
                       placeholder="Nueva contraseña">
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
                <input type="password" name="password_confirmation"
                       class="form-control guest-form-control"
                       placeholder="Confirmar nueva contraseña">
                <div class="input-group-append">
                    <span class="input-group-text guest-input-group-text">
                        <i class="fas fa-check-circle"></i>
                    </span>
                </div>
            </div>
        </div>

        <button type="submit" class="guest-btn-primary">
            <i class="fas fa-key mr-2"></i> Restablecer contraseña
        </button>
    </form>
@stop

@section('footer')
    <a href="{{ route('login') }}" class="guest-link-muted">
        <i class="fas fa-arrow-left mr-1"></i> Volver al login
    </a>
@stop