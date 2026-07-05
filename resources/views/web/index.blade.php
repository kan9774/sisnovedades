<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Armada de Comunicaciones') }}</title>

    <!-- AdminLTE / Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700;900&display=swap"
        rel="stylesheet">
    <!-- Landing CSS (todos los estilos personalizados) -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>

<body>

    <!-- ======= HEADER / NAVBAR ======= -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('image/logo/Heraldica.png') }}" alt="Ejército Nacional" class="img-fluid"
                    style="max-height: 40px;">
                <span class="d-none d-md-inline ml-2">{{ config('app.name') }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="#inicio">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#nosotros">Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#servicios">Servicios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contacto">Contacto</a>
                    </li>

                    @guest
                        <li class="nav-item d-lg-inline-block">
                            <a class="btn btn-sm ml-2 d-block d-lg-inline-block btn-login" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                            </a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item d-lg-inline-block">
                                <a class="btn btn-sm ml-1 d-block d-lg-inline-block btn-register"
                                    href="{{ route('register') }}">
                                    <i class="fas fa-user-plus"></i> Registrarse
                                </a>
                            </li>
                        @endif
                    @endauth
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown"
                                role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                @if (Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                        class="img-circle elevation-1" alt="Avatar"
                                        style="width: 28px; height: 28px; object-fit: cover; margin-right: 6px;">
                                @else
                                    <span class="avatar-circle">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                @endif
                                <span class="d-none d-md-inline">{{ Auth::user()->name }}
                                    {{ Auth::user()->last_name ?? '' }}</span>
                                <span class="d-inline d-md-none">{{ Auth::user()->name }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">

                                {{-- Info usuario --}}
                                <div class="dropdown-item disabled user-info-dropdown">
                                    <div class="user-name">
                                        {{ Auth::user()->name }} {{ Auth::user()->last_name ?? '' }}
                                    </div>
                                    <div class="user-role">
                                        <i class="fas fa-user-tag mr-1"></i> {{ Auth::user()->rol->name ?? 'Sin rol' }}
                                    </div>
                                    <div class="user-email">
                                        <i class="fas fa-envelope mr-1"></i> {{ Auth::user()->email }}
                                    </div>
                                </div>

                                {{-- Solo admin ve panel de control --}}
                                @can('viewAny', App\Models\User::class)
                                    <a class="dropdown-item" href="{{ route('admin.index') }}">
                                        <i class="fas fa-tachometer-alt"></i> Panel de control
                                    </a>
                                @endcan

                                {{-- Admin, capitán y oficial ven guardias --}}
                                @can('viewAny', App\Models\Guard::class)
                                    <a class="dropdown-item" href="{{ route('admin.guardias.index') }}">
                                        <i class="fas fa-shield-alt"></i> Guardias
                                    </a>
                                @endcan

                                {{-- Admin, capitán y oficial ven novedades del backend --}}
                                @can('viewAny', App\Models\Guard::class)
                                    <a class="dropdown-item" href="{{ route('admin.novedades.index') }}">
                                        <i class="fas fa-newspaper"></i> Novedades
                                    </a>
                                @endcan
                                {{-- Admin, puede ver Uauraios --}}
                                @can('viewAny', App\Models\User::class)
                                    <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                        <i class="fas fa-users"></i> Usuarios
                                    </a>
                                @endcan
                                {{-- Admin, puede Roles --}}
                                @can('viewAny', App\Models\User::class)
                                    <a class="dropdown-item" href="{{ route('admin.roles.index') }}">
                                        <i class="fas fa-key"></i> Roles
                                    </a>
                                @endcan
                                {{-- Admin, puede ver Permisos --}}
                                @can('viewAny', App\Models\User::class)
                                    <a class="dropdown-item" href="{{ route('admin.permisos.index') }}">
                                        <i class="fas fa-key"></i> Permisos
                                    </a>
                                @endcan

                                {{-- Solo admin ve organismos --}}
                                @can('viewAny', App\Models\User::class)
                                    <a class="dropdown-item" href="{{ route('admin.organismos.index') }}">
                                        <i class="fas fa-building"></i> Organismos
                                    </a>
                                @endcan

                                {{-- Visitante solo ve guardias cerradas --}}
                                @if (Auth::user()->rol?->name === 'visitante')
                                    <a class="dropdown-item" href="{{ route('novedades-publicas') }}">
                                        <i class="fas fa-newspaper"></i> Novedades
                                    </a>
                                @endif

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item text-danger" href="#"
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

    <!-- ======= HERO SECTION ======= -->
    <section id="inicio" class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title">Comunicaciones</h1>
            <h1 class="hero-title" style="font-size: 2.5rem; margin-top: -0.5rem;">Ejército Nacional</h1>
            <p class="hero-subtitle">Conectando el país, garantizando la seguridad y soberanía nacional</p>
            <div class="mt-5">
                <a href="#nosotros" class="text-white" style="font-size: 2rem; opacity: 0.7;">
                    <i class="fas fa-chevron-down"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ======= SOBRE NOSOTROS ======= -->
    <section id="nosotros" class="py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">Sobre Nosotros</h2>
                    <p class="text-muted lead">Somos el brazo tecnológico del Ejército, especializado en comunicaciones
                        tácticas y estratégicas.</p>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-md-4 value-item">
                    <i class="fas fa-bullseye"></i>
                    <h5>Misión</h5>
                    <p class="text-muted">Asegurar la transmisión de información crítica en todo el territorio
                        nacional,
                        apoyando las operaciones militares y la defensa del país.</p>
                </div>
                <div class="col-md-4 value-item">
                    <i class="fas fa-eye"></i>
                    <h5>Visión</h5>
                    <p class="text-muted">Ser la vanguardia en comunicaciones militares de la región, con tecnología de
                        punta y personal altamente capacitado.</p>
                </div>
                <div class="col-md-4 value-item">
                    <i class="fas fa-handshake"></i>
                    <h5>Valores</h5>
                    <p class="text-muted">Disciplina, integridad, trabajo en equipo, innovación y compromiso con la
                        patria.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= SERVICIOS ======= -->
    <section id="servicios" class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">Nuestros Servicios</h2>
                    <p class="text-muted lead">Soluciones integrales de comunicación para las Fuerzas Armadas</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="service-card text-center">
                        <div class="service-icon"><i class="fas fa-satellite"></i></div>
                        <h5>Comunicaciones Satelitales</h5>
                        <p class="text-muted small">Enlace satelital de alta capacidad para operaciones en cualquier
                            punto del país.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="service-card text-center">
                        <div class="service-icon"><i class="fas fa-radio"></i></div>
                        <h5>Redes HF / VHF / UHF</h5>
                        <p class="text-muted small">Redes de radio de largo alcance y comunicaciones tácticas en campo.
                        </p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="service-card text-center">
                        <div class="service-icon"><i class="fas fa-network-wired"></i></div>
                        <h5>Infraestructura de Red</h5>
                        <p class="text-muted small">Diseño e implementación de redes IP seguras y resilientes para
                            cuarteles.</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="service-card text-center">
                        <div class="service-icon"><i class="fas fa-lock"></i></div>
                        <h5>Ciberseguridad</h5>
                        <p class="text-muted small">Protección de la información y comunicaciones contra amenazas
                            cibernéticas.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= CONTACTO ======= -->
    <section id="contacto" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h2 class="section-title text-left" style="text-align:left !important;">
                        <span style="display:inline-block;">Contacto</span>
                    </h2>
                    <p class="text-muted">Ponte en contacto con la unidad de comunicaciones para más información.</p>
                    <ul class="list-unstyled mt-4">
                        <li><i class="fas fa-map-marker-alt text-primary mr-2"></i> Cuartel General, Ciudad de Buenos
                            Aires</li>
                        <li><i class="fas fa-phone text-primary mr-2"></i> +54 11 1234-5678</li>
                        <li><i class="fas fa-envelope text-primary mr-2"></i> comunicaciones@ejercito.mil.ar</li>
                        <li><i class="fas fa-clock text-primary mr-2"></i> Lunes a Viernes 08:00 - 17:00</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <form>
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre"
                                        placeholder="Tu nombre">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email"
                                        placeholder="tu@email.com">
                                </div>
                                <div class="form-group">
                                    <label for="mensaje">Mensaje</label>
                                    <textarea class="form-control" id="mensaje" rows="3" placeholder="Consulta..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Enviar mensaje</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= FOOTER ======= -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-satellite-dish mr-2"></i> Arma de Comunicaciones</h5>
                    <p class="small">Garantizando la conectividad y seguridad de las comunicaciones militares.</p>
                </div>
                <div class="col-md-3">
                    <h6>Enlaces rápidos</h6>
                    <ul class="list-unstyled">
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#nosotros">Nosotros</a></li>
                        <li><a href="#servicios">Servicios</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Redes sociales</h6>
                    <div class="d-flex">
                        <a href="#" class="mr-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="mr-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="mr-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr style="border-color: #2a3a4a;">
            <div class="text-center small">
                &copy; {{ date('Y') }} Arma de Comunicaciones del Ejército. Todos los derechos reservados.
            </div>
        </div>
    </footer>

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
        $(document).ready(function() {
            $('.dropdown-menu a').on('click', function() {
                if ($(window).width() <= 991) {
                    $('#navbarNav').collapse('hide');
                }
            });
        });
    </script>
</body>

</html>
