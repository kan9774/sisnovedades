<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Armada de Comunicaciones') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('image/logo/Heraldica.png') }}">
    <link rel="shortcut icon" href="{{ asset('image/logo/Heraldica.png') }}">

    <!-- AdminLTE / Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <!-- Google Fonts: Oswald (display), IBM Plex Mono (utilitaria), Inter (texto) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Landing CSS (todos los estilos personalizados) -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">

    @livewireStyles
</head>

<body>

    {{-- Alpine (empaquetado con Livewire 3/4) maneja qué "canal" está activo
         y si la sidebar está abierta (off-canvas en móvil).
         Shell de dos columnas: sidebar fija a la izquierda con navegación,
         estado de red y reloj; a la derecha, el contenido con scroll interno
         y el footer siempre visible al pie de esa misma columna. --}}
    <div x-data="{ seccion: 'inicio', sidebarOpen: false }" class="app-shell" :class="{ 'sidebar-open': sidebarOpen }"
        @cerrar-sidebar.window="sidebarOpen = false">

        <livewire:landing.navbar />

        <div class="content-col">
            <main class="app-main">
                <div x-show="seccion === 'inicio'" x-cloak>
                    <livewire:landing.hero />
                </div>
                <div x-show="seccion === 'nosotros'" x-cloak>
                    {{-- TODO: si "Organigrama" tiene su propio componente, moverlo a su propio
                         div con x-show="seccion === 'organigrama'" --}}
                    <livewire:landing.nosotros />
                </div>
                <div x-show="seccion === 'servicios'" x-cloak>
                    <livewire:landing.servicios />
                </div>
                <div x-show="seccion === 'documentos'" x-cloak>
                    <livewire:landing.documentos />
                </div>
                <div x-show="seccion === 'recreacion'" x-cloak>
                    <livewire:landing.recreacion />
                </div>
                <div x-show="seccion === 'novedades-cerradas'" x-cloak>
                    <livewire:landing.novedades-cerradas />
                </div>
                <div x-show="seccion === 'contacto'" x-cloak>
                    <livewire:landing.contacto-seccion />
                </div>
            </main>

            <livewire:landing.footer />
        </div>

    </div>

    {{-- Watcher de notificaciones: el usuario puede estar logueado desde afuera
         de la intranet (ej. accediendo por el dominio público), no solo desde
         el panel interno --}}
    @auth
        <livewire:notificaciones-watcher />
    @endauth

    <!-- ======= SCRIPTS ======= -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/fontawesome/js/all.min.js') }}"></script>
    <script>
        // Cerrar el menú colapsado en móvil al elegir una sección o un link del dropdown
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.dropdown-menu a, .dropdown-menu button').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 991) {
                        document.dispatchEvent(new CustomEvent('cerrar-sidebar'));
                    }
                });
            });
        });

        // Reloj de la consola (desktop + móvil)
        function actualizarReloj() {
            const ahora = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const texto = `${pad(ahora.getHours())}:${pad(ahora.getMinutes())}:${pad(ahora.getSeconds())}`;
            ['reloj-consola', 'reloj-consola-mobile'].forEach(function(id) {
                const el = document.getElementById(id);
                if (el) el.textContent = texto;
            });
        }
        actualizarReloj();
        setInterval(actualizarReloj, 1000);
    </script>

    {{-- Notificaciones de escritorio + sonido de aviso --}}
    @auth
        <script>
            (function() {
                const sonidoNotificacion = new Audio('{{ asset('sounds/notificacion.mp3') }}');
                sonidoNotificacion.volume = 0.6;

                document.addEventListener('click', function desbloquear() {
                    sonidoNotificacion.play().then(() => {
                        sonidoNotificacion.pause();
                        sonidoNotificacion.currentTime = 0;
                    }).catch(() => {});
                    document.removeEventListener('click', desbloquear);
                }, { once: true });

                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission();
                }

                document.addEventListener('livewire:init', function() {
                    Livewire.on('nueva-novedad', function(data) {
                        const payload = Array.isArray(data) ? data[0] : data;

                        sonidoNotificacion.play().catch(function(err) {
                            console.warn('No se pudo reproducir el sonido de notificación:', err);
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

    @livewireScripts
</body>

</html>