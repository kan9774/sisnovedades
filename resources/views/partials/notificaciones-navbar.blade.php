@php
    $notificacionesRecientes = [];
    $totalNoLeidas = 0;
    if (auth()->check()) {
        $notificacionesRecientes = auth()->user()->unreadNotifications()->latest()->take(5)->get();
        $totalNoLeidas = auth()->user()->unreadNotifications()->count();
    }
@endphp

<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-bell"></i>
        @if($totalNoLeidas > 0)
            <span class="badge badge-danger navbar-badge">{{ $totalNoLeidas }}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">{{ $totalNoLeidas }} notificaciones</span>
        <div class="dropdown-divider"></div>

        @forelse($notificacionesRecientes as $notificacion)
            <form action="{{ route('admin.notificaciones.leer', $notificacion->id) }}" method="POST" class="p-0 m-0">
                @csrf
                <button type="submit" class="dropdown-item text-wrap" style="white-space: normal;">
                    <i class="fas fa-exclamation-circle mr-2 text-danger"></i>
                    {{ $notificacion->data['mensaje'] ?? 'Nueva notificación' }}
                    <br>
                    <span class="text-muted text-sm">{{ $notificacion->created_at->diffForHumans() }}</span>
                </button>
            </form>
            <div class="dropdown-divider"></div>
        @empty
            <span class="dropdown-item text-muted">Sin notificaciones pendientes</span>
            <div class="dropdown-divider"></div>
        @endforelse

        <a href="{{ route('admin.notificaciones.index') }}" class="dropdown-item dropdown-footer">
            Ver todas las notificaciones
        </a>
    </div>
</li>