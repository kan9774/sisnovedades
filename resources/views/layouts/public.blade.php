<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>

    {{-- Navbar igual a la landing --}}
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="{{ asset('image/logo/Heraldica.png') }}" alt="Logo" style="max-height: 40px;">
                <span class="d-none d-md-inline ml-2">{{ config('app.name') }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="/">Inicio</a></li>
                    @guest
                        <li class="nav-item">
                            <a class="btn btn-outline-light btn-sm ml-2" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                            </a>
                        </li>
                        <li class="nav-item ml-2">
                            <a class="btn btn-light btn-sm" href="{{ route('register') }}">
                                <i class="fas fa-user-plus"></i> Registrarse
                            </a>
                        </li>
                    @endguest
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-toggle="dropdown">
                                <i class="fas fa-user-cog"></i> {{ Auth::user()->name }} {{ Auth::user()->last_name }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                @can('viewAny-user')
                                    <a class="dropdown-item" href="{{ route('admin.index') }}">
                                        <i class="fas fa-tachometer-alt"></i> Panel de control
                                    </a>
                                @endcan
                                @can('create', App\Models\Guard::class)
                                    <a class="dropdown-item" href="{{ route('admin.guardias.index') }}">
                                        <i class="fas fa-shield-alt"></i> Guardias
                                    </a>
                                @endcan
                                <a class="dropdown-item" href="{{ route('novedades-publicas') }}">
                                    <i class="fas fa-newspaper"></i> Novedades
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    {{-- Contenido de cada página --}}
    <div style="padding-top: 70px; min-height: calc(100vh - 120px);">
        @yield('content')
    </div>

    {{-- Footer --}}
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-satellite-dish mr-2"></i> Arma de Comunicaciones</h5>
                    <p class="small">Garantizando la conectividad y seguridad de las comunicaciones militares.</p>
                </div>
                <div class="col-md-6 text-right">
                    <p class="small mt-3">&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('js')
</body>
</html>