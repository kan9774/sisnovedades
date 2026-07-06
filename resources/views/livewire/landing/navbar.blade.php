<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
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

                            {{-- Admin, capitán y oficial ven documentos --}}
                            @can('viewAny', App\Models\Documento::class)
                                <a class="dropdown-item" href="{{ route('admin.documentos.index') }}">
                                    <i class="fas fa-file-alt"></i> Documentos
                                </a>
                            @endcan

                            {{-- Admin y encargados ven el palomar --}}
                            @can('viewAny', App\Models\Palomar::class)
                                <a class="dropdown-item" href="{{ route('admin.palomares.index') }}">
                                    <i class="fas fa-dove"></i> Palomar
                                </a>
                            @endcan

                            {{-- Admin, puede ver Usuarios --}}
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