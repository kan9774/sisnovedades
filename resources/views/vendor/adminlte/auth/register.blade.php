@extends('adminlte::auth.auth-page', ['authType' => 'register'])

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
        .register-page,
        body.register-page,
        .hold-transition.register-page {
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
        .register-box,
        .register-logo {
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
        .register-box {
            width: 450px !important;
            max-width: 95% !important;
            margin: 0 auto !important;
            position: relative !important;
            top: auto !important;
            transform: none !important;
            display: block !important;
        }

        /* Card con efecto vidrio */
        .register-page .card {
            border-radius: 15px !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
            background: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(10px) !important;
            margin: 0 !important;
        }

        .register-page .card-header {
            background: linear-gradient(135deg, #0a1e32, #1a3a5a) !important;
            border-bottom: 3px solid #f0c040 !important;
            padding: 25px 20px !important;
            text-align: center !important;
            border-radius: 15px 15px 0 0 !important;
        }

        .register-page .card-header h3 {
            color: #fff !important;
            font-weight: 700 !important;
            font-size: 1.5rem !important;
            margin: 0 !important;
        }

        .register-page .card-body {
            padding: 35px 30px !important;
            background: transparent !important;
        }

        /* Campos con borde redondeado */
        .register-page .form-control {
            border-radius: 50px 0 0 50px !important;
            padding: 12px 20px !important;
            border: 2px solid #e9ecef !important;
            border-right: none !important;
            height: auto !important;
        }

        .register-page .input-group-text {
            border-radius: 0 50px 50px 0 !important;
            background: #f8f9fa !important;
            border: 2px solid #e9ecef !important;
            border-left: none !important;
            padding: 0 20px !important;
        }

        .register-page .form-control:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15) !important;
        }

        /* Botón Registrarse */
        .register-page .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0a58ca) !important;
            border: none !important;
            border-radius: 50px !important;
            padding: 10px 20px !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3) !important;
            transition: all 0.3s ease !important;
        }

        .register-page .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4) !important;
        }

        /* Enlace "Ya tengo cuenta" */
        .register-page .card-footer {
            background: transparent !important;
            border-top: 1px solid rgba(0,0,0,0.05) !important;
            padding: 15px 30px !important;
            text-align: center !important;
        }

        .register-page .card-footer a {
            color: #0d6efd !important;
            text-decoration: none !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
        }

        .register-page .card-footer a:hover {
            color: #0a58ca !important;
            text-decoration: underline !important;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .register-box {
                width: 95% !important;
                max-width: 95% !important;
            }

            .register-page .card-body {
                padding: 25px 20px !important;
            }

            .register-page .card-header h3 {
                font-size: 1.2rem !important;
            }

            .register-page .form-control {
                font-size: 0.9rem !important;
                padding: 10px 15px !important;
            }
        }

        /* Para pantallas muy altas */
        @media (min-height: 900px) {
            .register-box {
                margin-top: 0 !important;
            }
        }
    </style>
@stop

@php
    $loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
    $registerUrl = View::getSection('register_url') ?? config('adminlte.register_url', 'register');

    if (config('adminlte.use_route_url', false)) {
        $loginUrl = $loginUrl ? route($loginUrl) : '';
        $registerUrl = $registerUrl ? route($registerUrl) : '';
    } else {
        $loginUrl = $loginUrl ? url($loginUrl) : '';
        $registerUrl = $registerUrl ? url($registerUrl) : '';
    }
@endphp

@section('auth_header', __('adminlte::adminlte.register_message'))

@section('auth_body')
    <form action="{{ $registerUrl }}" method="post">
        @csrf

        {{-- Name field --}}
        <div class="form-group">
            <div class="input-group mb-3">
                <input type="text" name="name" 
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" 
                       placeholder="{{ __('adminlte::adminlte.full_name') }}" 
                       autofocus>

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-user"></span>
                    </div>
                </div>

                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        {{-- Email field --}}
        <div class="form-group">
            <div class="input-group mb-3">
                <input type="email" name="email" 
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" 
                       placeholder="{{ __('adminlte::adminlte.email') }}">

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

        {{-- Confirm password field --}}
        <div class="form-group">
            <div class="input-group mb-3">
                <input type="password" name="password_confirmation"
                       class="form-control @error('password_confirmation') is-invalid @enderror"
                       placeholder="{{ __('adminlte::adminlte.retype_password') }}">

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>

                @error('password_confirmation')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        {{-- Register button --}}
        <button type="submit" class="btn btn-primary btn-block">
            <span class="fas fa-user-plus mr-2"></span>
            {{ __('adminlte::adminlte.register') }}
        </button>
    </form>
@stop

@section('auth_footer')
    <p class="my-0">
        <a href="{{ $loginUrl }}">
            <i class="fas fa-arrow-left mr-1"></i>
            {{ __('adminlte::adminlte.i_already_have_a_membership') }}
        </a>
    </p>
@stop