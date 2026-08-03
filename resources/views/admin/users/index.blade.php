@extends('layouts.app')

@section('subtitle', 'Usuarios')
@section('content_header_title', 'Usuarios')
@section('content_header_subtitle', 'Listado')

@section('content_body')
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if ($usersIncompletos->isNotEmpty())
            <div class="card card-outline-ops">
                <div class="card-header-ops">
                    <div class="card-header-ops__title-wrap">
                        <h3 class="card-title-ops mb-0">
                            <i class="fas fa-exclamation-triangle" style="color: #FFD200; margin-right: 8px;"></i>
                            Usuarios incompletos
                        </h3>
                        <span class="card-header-ops__eyebrow">{{ $usersIncompletos->count() }} registros pendientes</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-ops">
                            <tr>
                                <th>C.I.</th>
                                <th>Creado</th>
                                <th>Paso alcanzado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usersIncompletos as $user)
                                <tr>
                                    <td>{{ $user->ci_formateado ?? $user->ci }}</td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @php
                                            $completoPaso2 = $user->grado_id && $user->paseVigente();
                                        @endphp
                                        <div class="mini-wizard">
                                            @foreach (['Cédula', 'Grado / Unidad', 'Datos Personales'] as $idx => $etiqueta)
                                                @php
                                                    $stepNum = $idx + 1;
                                                    $isDone = $completoPaso2 ? $stepNum <= 2 : $stepNum === 1;
                                                    $isActive = !$isDone && $stepNum === ($completoPaso2 ? 3 : 2);
                                                @endphp
                                                <div
                                                    class="mini-wizard__step {{ $isActive ? 'is-active' : '' }} {{ $isDone ? 'is-done' : '' }}">
                                                    <div class="mini-wizard__circle">
                                                        @if ($isDone && $stepNum < 3)
                                                            <i class="fas fa-check"></i>
                                                        @elseif (!$isDone)
                                                            {{ $stepNum }}
                                                        @else
                                                            <i class="fas fa-lock"></i>
                                                        @endif
                                                    </div>
                                                    <div class="mini-wizard__label">{{ $etiqueta }}</div>
                                                </div>
                                                @if ($idx < 2)
                                                    <div class="mini-wizard__line {{ $isDone ? 'is-done' : '' }}"></div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ route('admin.users.create.resume', $user->id) }}"
                                                class="btn-ops btn-ops-info btn-xs mr-1" aria-label="Retomar wizard">
                                                <i class="fas fa-play"></i> Retomar
                                            </a>
                                            <form action="{{ route('admin.users.destroy-incompleto', $user->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Esto borra el registro por completo, incluido cualquier historial ya generado. ¿Continuar?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn-ops btn-ops-danger btn-xs"
                                                    aria-label="Eliminar por completo">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="card card-outline-ops">
            <div class="card-header-ops">
                <div class="card-header-ops__title-wrap">
                    <h3 class="card-title-ops mb-0">Usuarios del sistema</h3>
                    <span class="card-header-ops__eyebrow">{{ $users->total() }} registros</span>
                </div>
                <div class="card-tools">
                    <a href="{{ route('admin.users.userdelete') }}" class="btn-ops btn-ops-warning btn-sm mr-1"
                        aria-label="Ver usuarios inactivos">
                        <i class="fas fa-user-slash"></i> Inactivos
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn-ops btn-ops-primary btn-sm"
                        aria-label="Crear nuevo usuario">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </a>
                </div>
            </div>
            <div class="card-body pb-0 mb-2">
                <form method="GET" action="{{ route('admin.users.index') }}" class="form-inline">
                    <div class="input-group" style="max-width: 600px;">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Buscar por nombre, apellido, email o C.I.">
                        <div class="input-group-append">
                            <button type="submit" class="btn-ops btn-ops-primary" aria-label="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                            @if (request('search'))
                                <a href="{{ route('admin.users.index') }}" class="btn-ops btn-ops-secondary"
                                    aria-label="Limpiar búsqueda">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-ops">
                        <tr>
                            <th>Grado</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->grade }}</td>
                                <td>{{ $user->name }} {{ $user->last_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @forelse($user->roles as $rol)
                                        <span class="badge-ops badge-ops-info mr-1 mb-1">
                                            {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                                        </span>
                                    @empty
                                        <span class="badge-ops badge-ops-secondary mb-1">Sin rol</span>
                                    @endforelse
                                    @if ($user->isSuperAdmin())
                                        <span class="badge-ops badge-ops-dark mb-1">SuperAdmin</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->status === \App\Enums\UserStatus::Active)
                                        <span class="badge-ops badge-ops-success">Activo</span>
                                    @else
                                        <span class="badge-ops badge-ops-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center">
                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                            class="btn-ops btn-ops-warning btn-xs mr-1" aria-label="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-ops btn-ops-danger btn-xs" aria-label="Eliminar usuario">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="card-footer">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            $('.alert').delay(4000).fadeOut('slow');
        });
    </script>
@endpush
