@extends('layouts.guest')

@section('title', 'Recuperar contraseña')
@section('header_title', 'SIS-Novedades')
@section('header_subtitle', 'Recuperar contraseña')

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

    @if (session('status'))
        <div class="alert alert-success guest-alert alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('status') }}
        </div>
    @endif

    <p class="text-center guest-link-muted mb-3">
        Ingresá tu email y te enviaremos un enlace para restablecer tu contraseña.
    </p>

    <form action="{{ route('password.email') }}" method="POST">
        @csrf

        <div class="form-group">
            <div class="input-group mb-3">
                <input type="email" name="email"
                       class="form-control guest-form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
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

        <button type="submit" class="guest-btn-primary">
            <i class="fas fa-paper-plane mr-2"></i> Enviar enlace de recuperación
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="guest-link-muted">
            <i class="fas fa-arrow-left mr-1"></i> Volver al login
        </a>
    </div>
@stop

@section('footer')
    <a href="{{ route('home') }}" class="guest-link-muted">
        <i class="fas fa-arrow-left mr-1"></i> Volver al inicio
    </a>
@stop