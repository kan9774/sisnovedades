@extends('layouts.guest')

@section('title', 'Iniciar sesión')
@section('header_title', 'SIS-Novedades')
@section('header_subtitle', 'Autenticarse para iniciar sesión')

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

    <form action="{{ route('login') }}" method="POST">
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

        <div class="row align-items-center">
            <div class="col-7">
                <div class="icheck-primary guest-checkbox" title="Recordar sesión">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Recordarme</label>
                </div>
            </div>
            <div class="col-5">
                <button type="submit" class="guest-btn-primary">
                    <i class="fas fa-sign-in-alt mr-2"></i> Acceder
                </button>
            </div>
        </div>
    </form>

    <div class="text-center mt-3">
        @if(Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="guest-link-muted">
                <i class="fas fa-key mr-1"></i> Olvidé mi contraseña
            </a>
        @endif
    </div>
@stop

@section('footer')
    <a href="{{ route('home') }}" class="guest-link-muted">
        <i class="fas fa-arrow-left mr-1"></i> Volver al inicio
    </a>
@stop