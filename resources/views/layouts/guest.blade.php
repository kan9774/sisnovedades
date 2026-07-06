<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'SIS-Novedades'))</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('image/logo/Heraldica.png') }}">
    <link rel="shortcut icon" href="{{ asset('image/logo/Heraldica.png') }}">

    {{-- AdminLTE / Bootstrap CSS --}}
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700;900&display=swap"
        rel="stylesheet">

    {{-- Estilos personalizados para páginas guest --}}
    <style>
        /* ======================================================
           ESTILOS PARA PÁGINAS DE AUTENTICACIÓN (GUEST)
           ====================================================== */

        /* Resetear todos los estilos */
        html,
        body {
            height: 100% !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Source Sans Pro', sans-serif;
        }

        /* Forzar fondo gradiente y centrado */
        .guest-page {
            background: linear-gradient(160deg, #e3eeff 0%, #d4e4f7 30%, #e8f0fe 60%, #f0f4ff 100%) !important;
            min-height: 100vh !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            padding: 20px !important;
            position: relative !important;
        }

        /* Contenedor del formulario */
        .guest-box {
            width: 420px !important;
            max-width: 95% !important;
            margin: 0 auto !important;
            position: relative !important;
        }

        /* Card con efecto vidrio */
        .guest-card {
            border-radius: 15px !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
            background: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(10px) !important;
            overflow: hidden !important;
        }

        .guest-card-header {
            background: linear-gradient(135deg, #0a1e32, #1a3a5a) !important;
            border-bottom: 3px solid #f0c040 !important;
            padding: 25px 20px !important;
            text-align: center !important;
        }

        .guest-card-header h3 {
            color: #fff !important;
            font-weight: 700 !important;
            font-size: 1.3rem !important;
            margin: 0 !important;
        }

        .guest-card-header .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }

        .guest-card-body {
            padding: 35px 30px !important;
            background: transparent !important;
        }

        /* Campos con borde redondeado */
        .guest-form-control {
            border-radius: 50px 0 0 50px !important;
            padding: 12px 20px !important;
            border: 2px solid #e9ecef !important;
            border-right: none !important;
            height: auto !important;
            font-size: 0.95rem !important;
        }

        .guest-input-group-text {
            border-radius: 0 50px 50px 0 !important;
            background: #f8f9fa !important;
            border: 2px solid #e9ecef !important;
            border-left: none !important;
            padding: 0 18px !important;
            color: #6c757d !important;
        }

        .guest-form-control:focus {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15) !important;
        }

        /* Botón principal */
        .guest-btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0a58ca) !important;
            border: none !important;
            border-radius: 50px !important;
            padding: 12px 20px !important;
            font-weight: 600 !important;
            font-size: 1rem !important;
            color: #fff !important;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3) !important;
            transition: all 0.3s ease !important;
            width: 100% !important;
        }

        .guest-btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4) !important;
        }

        /* Enlaces */
        .guest-link {
            color: #0d6efd !important;
            text-decoration: none !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
        }

        .guest-link:hover {
            color: #0a58ca !important;
            text-decoration: underline !important;
        }

        .guest-link-muted {
            color: #6c757d !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
        }

        .guest-link-muted:hover {
            color: #0d6efd !important;
            text-decoration: underline !important;
        }

        /* Checkbox */
        .guest-checkbox {
            font-weight: 500 !important;
            color: #495057 !important;
        }

        .guest-checkbox input[type="checkbox"]:checked+label::before {
            background: #0d6efd !important;
            border-color: #0d6efd !important;
        }

        /* Alertas */
        .guest-alert {
            border-radius: 10px !important;
            border: none !important;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .guest-box {
                width: 95% !important;
            }

            .guest-card-body {
                padding: 25px 20px !important;
            }

            .guest-card-header h3 {
                font-size: 1.1rem !important;
            }

            .guest-card-header .logo {
                max-height: 45px;
            }

            .guest-form-control {
                font-size: 0.85rem !important;
                padding: 10px 15px !important;
            }
        }

        /* Para pantallas muy altas */
        @media (min-height: 900px) {
            .guest-box {
                margin-top: 0 !important;
            }
        }
    </style>

    {{-- Custom CSS adicional --}}
    @stack('styles')
</head>

<body>
    <div class="guest-page">
        <div class="guest-box">
            <div class="guest-card">
                {{-- Header con logo --}}
                <div class="guest-card-header">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('image/logo/Heraldica.png') }}" alt="Logo" class="logo">
                        <h3>@yield('header_title', config('app.name'))</h3>
                        @hasSection('header_subtitle')
                            <p class="text-white-50 mb-0" style="font-size: 0.9rem; margin-top: 4px;">
                                @yield('header_subtitle')
                            </p>
                        @endif
                    </a>
                </div>

                {{-- Body --}}
                <div class="guest-card-body">
                    @yield('content')
                </div>
            </div>

            {{-- Footer opcional --}}
            @hasSection('footer')
                <div class="text-center mt-3 text-muted" style="font-size: 0.85rem;">
                    @yield('footer')
                </div>
            @endif
        </div>
    </div>

    {{-- Scripts --}}
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    {{-- Scripts adicionales --}}
    @stack('scripts')
</body>

</html>