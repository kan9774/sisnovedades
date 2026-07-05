@extends('layouts.app')

@section('subtitle', 'Roles')
@section('content_header_title', 'Roles')
@section('content_header_subtitle', 'Listado')

@section('content_body')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users-cog"></i> Roles del sistema
            </h3>
            <div class="card-tools">
                @can('create', App\Models\Rol::class)
                    <a href="{{ route('admin.roles.create') }}" 
                       class="btn btn-outline-primary btn-sm"
                       style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);"
                       aria-label="Crear nuevo rol">
                        <i class="fas fa-plus-circle"></i> Nuevo Rol
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Permisos</th>
                        <th>Usuarios</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $rol)
                        <tr>
                            <td>
                                <strong>{{ ucfirst(str_replace('_', ' ', $rol->name)) }}</strong>
                            </td>
                            <td>{{ $rol->description ?? '-' }}</td>
                            <td>
                                @forelse($rol->permisos as $permiso)
                                    <span class="badge badge-info mr-1 mb-1">
                                        {{ str_replace('_', ' ', $permiso->name) }}
                                    </span>
                                @empty
                                    <span class="badge badge-secondary mb-1">Sin permisos</span>
                                @endforelse
                            </td>
                            <td>{{ $rol->users->count() }}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    @can('update', $rol)
                                        <a href="{{ route('admin.roles.edit', $rol) }}" 
                                           class="btn btn-outline-warning btn-xs mr-1"
                                           style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);"
                                           aria-label="Editar rol">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $rol)
                                        <form action="{{ route('admin.roles.destroy', $rol) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este rol?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-xs"
                                                    style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                                    aria-label="Eliminar rol">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay roles registrados.
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