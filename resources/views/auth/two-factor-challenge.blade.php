@extends('layouts.guest')

@section('title', 'Verificación en dos pasos')
@section('header_title', 'SIS-Novedades')
@section('header_subtitle', 'Verificación en dos pasos')

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

    <div id="code-form">
        <p class="text-center guest-link-muted mb-3">
            Ingresá el código de 6 dígitos de tu app autenticadora.
        </p>

        <form action="{{ route('two-factor.login') }}" method="POST">
            @csrf

            <div class="form-group">
                <div class="input-group mb-3">
                    <input type="text" name="code"
                           class="form-control guest-form-control"
                           placeholder="Código"
                           inputmode="numeric"
                           autofocus>
                    <div class="input-group-append">
                        <span class="input-group-text guest-input-group-text">
                            <i class="fas fa-shield-alt"></i>
                        </span>
                    </div>
                </div>
            </div>

            <button type="submit" class="guest-btn-primary">
                <i class="fas fa-check-circle mr-2"></i> Verificar
            </button>
        </form>
    </div>

    <div class="text-center mt-3">
        <a href="#" class="guest-link-muted" onclick="event.preventDefault(); toggleRecovery();">
            <i class="fas fa-life-ring mr-1"></i> Usar código de recuperación
        </a>
    </div>

    <div id="recovery-form" style="display:none;">
        <p class="text-center guest-link-muted mb-3 mt-3">
            Ingresá uno de tus códigos de recuperación.
        </p>

        <form action="{{ route('two-factor.login') }}" method="POST">
            @csrf

            <div class="form-group">
                <div class="input-group mb-3">
                    <input type="text" name="recovery_code"
                           class="form-control guest-form-control"
                           placeholder="Código de recuperación">
                    <div class="input-group-append">
                        <span class="input-group-text guest-input-group-text">
                            <i class="fas fa-key"></i>
                        </span>
                    </div>
                </div>
            </div>

            <button type="submit" class="guest-btn-primary">
                <i class="fas fa-check-circle mr-2"></i> Verificar
            </button>
        </form>
    </div>

    <script>
        function toggleRecovery() {
            document.getElementById('code-form').style.display = 'none';
            document.getElementById('recovery-form').style.display = 'block';
        }
    </script>
@stop

@section('footer')
    <a href="{{ route('login') }}" class="guest-link-muted">
        <i class="fas fa-arrow-left mr-1"></i> Volver al login
    </a>
@stop