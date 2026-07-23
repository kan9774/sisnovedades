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

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Usuarios del sistema</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.users.userdelete') }}" class="btn btn-outline-secondary btn-sm mr-1"
                        style="background-color: rgba(108, 117, 125, 0.08);" aria-label="Ver usuarios inactivos">
                        <i class="fas fa-user-slash"></i> Inactivos
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary btn-sm"
                        style="background-color: rgba(0, 123, 255, 0.08);" aria-label="Crear nuevo usuario">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
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
                                        <span class="badge badge-info mr-1 mb-1">
                                            {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                                        </span>
                                    @empty
                                        <span class="badge badge-secondary mb-1">Sin rol</span>
                                    @endforelse
                                    @if($user->isSuperAdmin())
                                        <span class="badge badge-dark mb-1">SuperAdmin</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->status === 'active')
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center">
                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                            class="btn btn-outline-warning btn-xs mr-1"
                                            style="background-color: rgba(255, 193, 7, 0.08);" aria-label="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-xs"
                                                style="background-color: rgba(220, 53, 69, 0.08);"
                                                aria-label="Eliminar usuario">
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