{{-- User menu dropdown --}}
@php
    $user = Auth::user();
@endphp

<li class="nav-item dropdown user-menu">
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        {{-- Avatar del usuario --}}
        @if($user->avatar)
            <img src="{{ asset('storage/' . $user->avatar) }}" 
                 class="user-image img-circle elevation-2" 
                 alt="Avatar"
                 style="width: 32px; height: 32px; object-fit: cover;">
        @else
            <span class="user-image d-inline-block text-center" 
                  style="width: 32px; height: 32px; line-height: 32px; border-radius: 50%; background: rgba(0,123,255,0.1); color: #0d6efd; font-weight: bold; font-size: 14px;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </span>
        @endif

        {{-- Nombre del usuario (visible en desktop) --}}
        <span class="d-none d-md-inline ml-1">
            {{ $user->name }} {{ $user->last_name ?? '' }}
        </span>
        <span class="d-none d-md-inline">
            <i class="fas fa-chevron-down" style="font-size: 0.65rem; color: rgba(255,255,255,0.5);"></i>
        </span>
    </a>

   <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
    {{-- Cabecera con avatar --}}
    <li class="user-header" style="background: #343a40; color: #fff; padding: 20px 15px 15px; border-radius: 4px 4px 0 0;">
        @if($user->avatar)
            <img src="{{ asset('storage/' . $user->avatar) }}" 
                 class="img-circle elevation-2" 
                 alt="Avatar"
                 style="width: 90px; height: 90px; object-fit: cover;">
        @else
            <div class="d-flex justify-content-center mb-2">
                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white"
                     style="width: 90px; height: 90px; font-size: 36px; font-weight: bold;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            </div>
        @endif
    </li>

    {{-- Información del usuario (sin duplicados) --}}
    <li class="user-body" style="padding: 12px 20px; background: #fff; border-bottom: 1px solid #dee2e6;">
        <p class="mb-0" style="line-height: 1.6;">
            <strong style="font-size: 1.05rem;">{{ $user->name }} {{ $user->last_name ?? '' }}</strong>
            <br>
            <small style="color: #6c757d !important;">
                <i class="fas fa-user-tag mr-1"></i> 
                {{ $user->rol->name ?? 'Sin rol' }}
            </small>
            <br>
            <small style="color: #6c757d !important; display: block; margin-top: 4px;">
                <i class="fas fa-envelope mr-1"></i> {{ $user->email }}
            </small>
        </p>
    </li>

    {{-- Footer con botones --}}
    <li class="user-footer" style="background: #f8f9fa; padding: 12px 15px; border-radius: 0 0 4px 4px; display: flex; justify-content: space-between; gap: 8px;">
        <a href="{{ route('logout') }}" 
           class="btn btn-default btn-flat btn-sm"
           style="border: 1px solid #ddd; flex: 1; text-align: center;"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </li>
</ul>
</li>