{{-- Contenedor raíz único (requisito de Livewire): display:contents lo saca
     del flujo, sus hijos actúan como si fueran hijos directos de .app-shell --}}
<div style="display: contents;">

{{-- ============================================================
     TOPBAR MÓVIL — solo visible <992px, dispara el off-canvas
     ============================================================ --}}
<div class="mobile-topbar">
    <button class="sidebar-toggle" type="button" @click="sidebarOpen = true" aria-label="Abrir menú">
        <i class="fas fa-bars"></i>
    </button>
    <a class="mobile-topbar__brand" href="{{ route('home') }}">
        <img src="{{ asset('image/logo/Heraldica.png') }}" alt="Ejército Nacional">
        <span>{{ config('app.name') }}</span>
    </a>
    <span class="mobile-topbar__clock" id="reloj-consola-mobile">--:--:--</span>
</div>

{{-- Fondo oscuro al abrir la sidebar en móvil --}}
<div class="sidebar-backdrop" x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
    style="display: none;"></div>

{{-- ============================================================
     SIDEBAR
     ============================================================ --}}
<aside class="sidebar">

    <a class="sidebar-brand" href="{{ route('home') }}">
        <img src="{{ asset('image/logo/Heraldica.png') }}" alt="Ejército Nacional">
        <span class="sidebar-brand__text">
            {{ config('app.name') }}
            <small>BCOM1 // Ejército Nacional</small>
        </span>
    </a>

    <nav class="sidebar-nav">
        <a href="#" class="sidebar-link" @click.prevent="seccion = 'inicio'; sidebarOpen = false"
            :class="{ 'sidebar-link-active': seccion === 'inicio' }">
            <span class="sidebar-link__ch">01</span>
            <i class="fa-solid fa-house"></i>
            <span>Inicio</span>
        </a>
        <a href="#" class="sidebar-link" @click.prevent="seccion = 'nosotros'; sidebarOpen = false"
            :class="{ 'sidebar-link-active': seccion === 'nosotros' }">
            <span class="sidebar-link__ch">02</span>
            <i class="fas fa-users"></i>
            <span>Nosotros</span>
        </a>
        <a href="#" class="sidebar-link" @click.prevent="seccion = 'servicios'; sidebarOpen = false"
            :class="{ 'sidebar-link-active': seccion === 'servicios' }">
            <span class="sidebar-link__ch">03</span>
            <i class="fa-solid fa-satellite-dish"></i>
            <span>Servicios</span>
        </a>
        <a href="#" class="sidebar-link" @click.prevent="seccion = 'contacto'; sidebarOpen = false"
            :class="{ 'sidebar-link-active': seccion === 'contacto' }">
            <span class="sidebar-link__ch">04</span>
            <i class="fas fa-envelope"></i>
            <span>Contacto</span>
        </a>
    </nav>

    <div class="sidebar-spacer"></div>

    @guest
        <div class="sidebar-auth">
            <a class="btn btn-sm btn-login" href="{{ route('login') }}">
                <i class="fas fa-sign-in-alt"></i> Iniciar sesión
            </a>
            @if (Route::has('register'))
                <a class="btn btn-sm btn-register" href="{{ route('register') }}">
                    <i class="fas fa-user-plus"></i> Registrarse
                </a>
            @endif
        </div>
    @endguest

    @auth
        {{-- Notificaciones --}}
        @php
            $totalNoLeidas = Auth::user()->unreadNotifications()->count();
        @endphp
        <div class="dropup px-3">
            <a href="#" class="sidebar-user-notif d-flex align-items-center" id="notifDropdown" role="button"
                data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <span class="ml-2" style="font-family: var(--font-mono); font-size: 0.7rem; letter-spacing: 0.06em; text-transform: uppercase;">
                    Notificaciones
                </span>
                @if ($totalNoLeidas > 0)
                    <span class="badge badge-danger navbar-badge-notif">{{ $totalNoLeidas }}</span>
                @endif
            </a>
            <div class="dropdown-menu">
                <span class="dropdown-item disabled">
                    <strong>{{ $totalNoLeidas }} notificaciones</strong>
                </span>
                <div class="dropdown-divider"></div>

                @php
                    $notificacionesRecientes = Auth::user()
                        ->unreadNotifications()
                        ->latest()
                        ->take(5)
                        ->get();
                @endphp

                @forelse ($notificacionesRecientes as $notificacion)
                    <form action="{{ route('admin.notificaciones.leer', $notificacion->id) }}" method="POST"
                        class="p-0 m-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-wrap" style="white-space: normal;">
                            <i class="fas fa-exclamation-circle mr-2 text-danger"></i>
                            {{ $notificacion->data['mensaje'] ?? 'Nueva notificación' }}
                            <br>
                            <small class="text-muted">{{ $notificacion->created_at->diffForHumans() }}</small>
                        </button>
                    </form>
                @empty
                    <span class="dropdown-item text-muted">Sin notificaciones pendientes</span>
                @endforelse

                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.notificaciones.index') }}" class="dropdown-item text-center">
                    Ver todas
                </a>
            </div>
        </div>

        {{-- Usuario --}}
        <div class="dropup">
            <div class="sidebar-user dropdown-toggle" id="navbarDropdown" role="button" data-toggle="dropdown"
                data-display="static"
                aria-haspopup="true" aria-expanded="false">
                @if (Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="img-circle elevation-1"
                        alt="Avatar" style="width: 30px; height: 30px; object-fit: cover; border-radius: 2px;">
                @else
                    <span class="avatar-circle">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                @endif
                <div class="sidebar-user__info">
                    <div class="sidebar-user__name">{{ Auth::user()->name }} {{ Auth::user()->last_name ?? '' }}</div>
                    <div class="sidebar-user__role">{{ Auth::user()->rol->name ?? 'Sin rol' }}</div>
                </div>
                <i class="fas fa-ellipsis-v text-muted"></i>
            </div>
            <div class="dropdown-menu">

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

                @can('viewAny', App\Models\User::class)
                    <a class="dropdown-item" href="{{ route('admin.index') }}">
                        <i class="fas fa-tachometer-alt"></i> Panel de Administrativo
                    </a>
                @endcan
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
        </div>
    @endauth

    {{-- Readout de estado: red, canal activo y reloj --}}
    <div class="sidebar-status">
        <div class="sidebar-status__row">
            <span><span class="status-dot"></span>RED</span>
            <strong>OPERATIVO</strong>
        </div>
        <div class="sidebar-status__row">
            <span>CANAL</span>
            <strong x-text="seccion.toUpperCase()"></strong>
        </div>
        <div class="sidebar-status__row">
            <span>HORA</span>
            <span class="sidebar-status__clock" id="reloj-consola">--:--:--</span>
        </div>
    </div>
</aside>

</div>{{-- /root wrapper --}}