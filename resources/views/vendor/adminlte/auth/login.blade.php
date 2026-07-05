@extends('adminlte::auth.auth-page', ['authType' => 'login'])

{{-- Agregar CSS directamente en el head --}}
@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

{{-- Estilos inline forzados --}}
@section('adminlte_css')
    <style>
        /* ======================================================
           FORZAR CENTRADO VERTICAL Y HORIZONTAL
           ====================================================== */

        /* Resetear todos los estilos de AdminLTE que afectan al posicionamiento */
        html, body {
            height: 100% !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Forzar fondo y centrado */
        .login-page,
        body.login-page,
        .hold-transition.login-page {
            background: linear-gradient(160deg, #e3eeff 0%, #d4e4f7 30%, #e8f0fe 60%, #f0f4ff 100%) !important;
            min-height: 100vh !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            padding: 0 !important;
            position: relative !important;
        }

        /* Resetear el wrapper de AdminLTE */
        .wrapper,
        .login-box,
        .login-logo,
        .register-box {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            transform: none !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
        }

        /* Contenedor del formulario */
        .login-box {
            width: 400px !important;
            max-width: 95% !important;
            margin: 0 auto !important;
            position: relative !important;
            top: auto !important;
            transform: none !important;
            display: block !important;
        }

        /* Card con efecto vidrio */
        .login-page .card {
            border-radius: 15px !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
            background: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(10px) !important;
            margin: 0 !important;
        }

        .login-page .card-header {
            background: linear-gradient(135deg, #0a1e32, #1a3a5a) !important;
            border-bottom: 3px solid #f0c040 !important;
            padding: 25px 20px !important;
            text-align: center !important;
            border-radius: 15px 15px 0 0 !important;
        }

        .login-page .card-header h3 {
            color: #fff !important;
            font-weight: 700 !important;
            font-size: 1.5rem !important;
            margin: 0 !important;
        }

        .login-page .card-body {
            padding: 35px 30px !important;
            background: transparent !important;
        }

        /* Campos con borde redondeado */
        .login-page .form-control {
            border-radius: 50px 0 0 50px !important;
            padding: 12px 20px !important;
            border: 2px solid #e9ecef !important;
            border-right: none !important;
            height: auto !important;
        }

        .login-page .input-group-text {
            border-radius: 0 50px 50px 0 !important;
            background: #f8f9fa !important;
            border: 2px solid #e9ecef !important;
            border-left: none !important;
            padding: 0 20px !important;
        }

        .login-page .form-control:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15) !important;
        }

        /* Botón Acceder */
        .login-page .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0a58ca) !important;
            border: none !important;
            border-radius: 50px !important;
            padding: 10px 20px !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3) !important;
            transition: all 0.3s ease !important;
        }

        .login-page .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4) !important;
        }

        /* Checkbox */
        .icheck-primary input[type="checkbox"]:checked + label::before {
            background: #0d6efd !important;
            border-color: #0d6efd !important;
        }

        .icheck-primary input[type="checkbox"]:focus + label::before {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25) !important;
        }

        /* Asegurar que el footer del card esté bien */
        .login-page .card-footer {
            background: transparent !important;
            border-top: 1px solid rgba(0,0,0,0.05) !important;
            padding: 15px 30px !important;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-box {
                width: 95% !important;
                max-width: 95% !important;
            }

            .login-page .card-body {
                padding: 25px 20px !important;
            }

            .login-page .card-header h3 {
                font-size: 1.2rem !important;
            }

            .login-page .row .col-7,
            .login-page .row .col-5 {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            .login-page .row .col-7 {
                margin-bottom: 12px;
                text-align: center;
            }

            .login-page .row .col-5 button {
                width: 100% !important;
            }
        }

        /* Para pantallas muy altas */
        @media (min-height: 900px) {
            .login-box {
                margin-top: 0 !important;
            }
        }
    </style>
@stop

@php
    $loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
    $passResetUrl = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset');

    if (config('adminlte.use_route_url', false)) {
        $loginUrl = $loginUrl ? route($loginUrl) : '';
        $passResetUrl = $passResetUrl ? route($passResetUrl) : '';
    } else {
        $loginUrl = $loginUrl ? url($loginUrl) : '';
        $passResetUrl = $passResetUrl ? url($passResetUrl) : '';
    }
@endphp

@section('auth_header', __('adminlte::adminlte.login_message'))

@section('auth_body')
    <form action="{{ $loginUrl }}" method="post">
        @csrf

        {{-- Email field --}}
        <div class="form-group">
            <div class="input-group mb-3">
                <input type="email" name="email" 
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" 
                       placeholder="{{ __('adminlte::adminlte.email') }}" 
                       autofocus>

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope"></span>
                    </div>
                </div>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        {{-- Password field --}}
        <div class="form-group">
            <div class="input-group mb-3">
                <input type="password" name="password" 
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ __('adminlte::adminlte.password') }}">

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        {{-- Login field --}}
        <div class="row align-items-center">
            <div class="col-7">
                <div class="icheck-primary" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">
                        {{ __('adminlte::adminlte.remember_me') }}
                    </label>
                </div>
            </div>

            <div class="col-5">
                <button type="submit" class="btn btn-primary btn-block">
                    <span class="fas fa-sign-in-alt mr-2"></span>
                    {{ __('adminlte::adminlte.sign_in') }}
                </button>
            </div>
        </div>
    </form>
@stop

@section('auth_footer')
    <div class="text-center mt-3">
        @if($passResetUrl)
            <p class="mb-0">
                <a href="{{ $passResetUrl }}" 
                   style="color: #6c757d; text-decoration: none; font-weight: 500; transition: all 0.2s ease;"
                   onmouseover="this.style.color='#0d6efd';"
                   onmouseout="this.style.color='#6c757d';">
                    <i class="fas fa-key mr-1"></i> {{ __('adminlte::adminlte.i_forgot_my_password') }}
                </a>
            </p>
        @endif
    </div>
@stop