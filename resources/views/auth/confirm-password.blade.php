@extends('layouts.guest')

@section('title', 'Confirmar contraseña')
@section('header_title', 'SIS-Novedades')
@section('header_subtitle', 'Confirmá tu contraseña para continuar')

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

    <p class="text-center guest-link-muted mb-3">
        Esta es un área protegida. Por favor confirmá tu contraseña antes de continuar.
    </p>

    <form action="{{ route('password.confirm') }}" method="POST">
        @csrf

        <div class="form-group">
            <div class="input-group mb-3">
                <input type="password" name="password"
                       class="form-control guest-form-control @error('password') is-invalid @enderror"
                       placeholder="Contraseña"
                       autofocus>
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

        <button type="submit" class="guest-btn-primary">
            <i class="fas fa-check-circle mr-2"></i> Confirmar
        </button>
    </form>
@stop

@section('footer')
    <a href="{{ route('home') }}" class="guest-link-muted">
        <i class="fas fa-arrow-left mr-1"></i> Volver al inicio
    </a>
@stop