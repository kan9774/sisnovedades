@extends('layouts.app')

@section('subtitle', 'Permisos')
@section('content_header_title', 'Permisos')
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
                <i class="fas fa-key"></i> Permisos del sistema
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.permisos.create') }}" 
                   class="btn btn-outline-primary btn-sm"
                   style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);"
                   aria-label="Crear nuevo permiso">
                    <i class="fas fa-plus-circle"></i> Nuevo Permiso
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Clave</th>
                        <th>Descripción</th>
                        <th>Roles</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permisos as $permiso)
                        <tr>
                            <td><code>{{ $permiso->name }}</code></td>
                            <td>{{ $permiso->description ?? '-' }}</td>
                            <td>{{ $permiso->rols->count() }}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('admin.permisos.edit', $permiso) }}"
                                       class="btn btn-outline-warning btn-xs mr-1"
                                       style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);"
                                       aria-label="Editar permiso">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.permisos.destroy', $permiso) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este permiso?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-xs"
                                                style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                                aria-label="Eliminar permiso">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No hay permisos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($permisos->hasPages())
            <div class="card-footer">
                {{ $permisos->links() }}
            </div>
        @endif
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