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
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700;900&display=swap"
        rel="stylesheet">
    <!-- Landing CSS (todos los estilos personalizados) -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">

    @livewireStyles
</head>

<body>

    <livewire:landing.navbar />

    <livewire:landing.hero />

    <livewire:landing.nosotros />

    <livewire:landing.servicios />

    <livewire:landing.contacto-seccion />

    <livewire:landing.footer />

    <!-- ======= SCRIPTS ======= -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        // Smooth scrolling para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offset = 70; // altura del navbar fijo
                    const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({
                        top: top,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Cerrar dropdown al hacer clic en un enlace en móvil
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.dropdown-menu a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 991) {
                        $('#navbarNav').collapse('hide');
                    }
                });
            });
        });
    </script>

    @livewireScripts
</body>

</html>