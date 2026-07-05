@extends('layouts.app')

@section('subtitle', 'Conductores')
@section('content_header_title', 'Conductores')
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
                <i class="fas fa-user-tie"></i> Conductores
            </h3>
            <div class="card-tools">
                @can('create', App\Models\Conductor::class)
                    <a href="{{ route('admin.conductores.create') }}"
                       class="btn btn-outline-primary btn-sm"
                       style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);"
                       aria-label="Crear nuevo conductor">
                        <i class="fas fa-plus-circle"></i> Nuevo Conductor
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Grado</th>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Licencia</th>
                        <th>Categoría</th>
                        <th>Venc. Licencia</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conductores as $conductor)
                        <tr>
                            <td>{{ $conductor->grado }}</td>
                            <td>{{ $conductor->nombre_completo }}</td>
                            <td>{{ $conductor->documento }}</td>
                            <td>{{ $conductor->nro_licencia }}</td>
                            <td>{{ $conductor->categoria_licencia }}</td>
                            <td>
                                @if($conductor->licencia_vigente)
                                    <span class="badge badge-success">{{ $conductor->fecha_vencimiento_licencia->format('d/m/Y') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ $conductor->fecha_vencimiento_licencia->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($conductor->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    @can('update', $conductor)
                                        <a href="{{ route('admin.conductores.edit', $conductor) }}"
                                           class="btn btn-outline-warning btn-xs mr-1"
                                           style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);"
                                           aria-label="Editar conductor">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $conductor)
                                        <form action="{{ route('admin.conductores.destroy', $conductor) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este conductor?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-xs"
                                                    style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                                    aria-label="Eliminar conductor">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-user-tie fa-2x d-block mb-2"></i>
                                No hay conductores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($conductores->hasPages())
            <div class="card-footer">
                {{ $conductores->links() }}
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