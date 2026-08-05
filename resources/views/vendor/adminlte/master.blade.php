<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- IFrame Preloader Removal Workaround --}}
    <!-- IFrame Preloader Removal Workaround -->
    <style type="text/css">
        body.iframe-mode .preloader {
            display: none !important;
        }
    </style>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets (depends on Laravel asset bundling tool) --}}
    @if (config('adminlte.enabled_laravel_mix', false))
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_css_path', 'css/app.css')) }}">
            @break

            @case('vite')
                @vite([config('adminlte.laravel_css_path', 'resources/css/app.css'), config('adminlte.laravel_js_path', 'resources/js/app.js')])
            @break

            @case('vite_js_only')
                @vite(config('adminlte.laravel_js_path', 'resources/js/app.js'))
            @break

            @default
                <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

                @if (config('adminlte.google_fonts.allowed', true))
                    <link rel="stylesheet"
                        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
                @endif
        @endswitch
    @endif

    {{-- Extra Configured Plugins Stylesheets --}}
    @include('adminlte::plugins', ['type' => 'css'])

    {{-- Estilos compartidos del panel "ops" (BCOM1) --}}
    <link rel="stylesheet" href="{{ asset('css/ops-panel.css') }}">

    {{-- Livewire Styles --}}
    @if (config('adminlte.livewire'))
        <livewire:styles />
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
    @if (config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" crossorigin="use-credentials" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicons/ms-icon-144x144.png') }}">
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')

    {{-- Watcher global de notificaciones (novedades, correos fallidos, etc.) --}}
    @auth
        <livewire:notificaciones-watcher />
    @endauth

    {{-- Base Scripts (depends on Laravel asset bundling tool) --}}
    @if (config('adminlte.enabled_laravel_mix', false))
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <script src="{{ mix(config('adminlte.laravel_js_path', 'js/app.js')) }}"></script>
            @break

            @case('vite')
            @case('vite_js_only')
            @break

            @default
                <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
                <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
                <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
                <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
        @endswitch
    @endif

    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Scripts --}}
    @if (config('adminlte.livewire'))
        <livewire:scripts />
    @endif

    {{-- Notificaciones de escritorio + sonido de aviso --}}
    @auth
        <script>
            (function() {
                const sonidoNotificacion = new Audio('{{ asset('sounds/notificacion.mp3') }}');
                sonidoNotificacion.volume = 0.6;

                // Desbloquea el autoplay del audio en el primer click del usuario,
                // por si la pestaña quedó abierta desde antes sin interacción.
                document.addEventListener('click', function desbloquear() {
                    sonidoNotificacion.play().then(() => {
                        sonidoNotificacion.pause();
                        sonidoNotificacion.currentTime = 0;
                    }).catch(() => {});
                    document.removeEventListener('click', desbloquear);
                }, { once: true });

                // Pide permiso de notificaciones del navegador una sola vez,
                // sin bloquear la carga de la página.
                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission();
                }

                document.addEventListener('livewire:init', function() {
                    Livewire.on('nueva-novedad', function(data) {
                        const payload = Array.isArray(data) ? data[0] : data;

                        sonidoNotificacion.play().catch(function(err) {
                            console.warn('No se pudo reproducir el sonido de notificación:', err);
                        });

                        // Badge de la campanita del navbar admin (Blade plano,
                        // no reactivo — lo actualizamos a mano acá).
                        document.querySelectorAll('.navbar-badge').forEach(function(badge) {
                            const actual = parseInt(badge.textContent, 10) || 0;
                            badge.textContent = actual + 1;
                            badge.style.display = '';
                        });

                        if (!('Notification' in window) || Notification.permission !== 'granted') {
                            return;
                        }

                        const notif = new Notification(payload.titulo, {
                            body: payload.cuerpo,
                            icon: '{{ asset('image/logo/Heraldica.png') }}',
                            tag: 'sisnovedades',
                        });

                        notif.onclick = function() {
                            window.focus();
                            if (payload.url) {
                                window.location.href = payload.url;
                            }
                            notif.close();
                        };
                    });
                });
            })();
        </script>
    @endauth

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

</body>

</html>