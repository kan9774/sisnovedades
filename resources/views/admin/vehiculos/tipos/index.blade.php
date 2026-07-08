@extends('layouts.app')

@section('subtitle', 'Tipos de Vehículo')
@section('content_header_title', 'Vehículos')
@section('content_header_subtitle', 'Tipos')

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
                <i class="fas fa-shapes"></i> Tipos de Vehículo
            </h3>
            <div class="card-tools">
                @can('create', App\Models\TipoVehiculo::class)
                    <a href="{{ route('admin.vehiculos.tipos.create') }}"
                       class="btn btn-outline-primary btn-sm"
                       style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                        <i class="fas fa-plus-circle"></i> Nuevo Tipo
                    </a>
                @endcan
                <a href="{{ route('admin.vehiculos.index') }}"
                   class="btn btn-outline-secondary btn-sm"
                   style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                    <i class="fas fa-arrow-left"></i> Volver a Vehículos
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tiposVehiculo as $tipo)
                        <tr>
                            <td>{{ $tipo->nombre }}</td>
                            <td>
                                @if($tipo->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    @can('update', $tipo)
                                        <a href="{{ route('admin.vehiculos.tipos.edit', $tipo) }}"
                                           class="btn btn-outline-warning btn-xs mr-1"
                                           style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $tipo)
                                        <form action="{{ route('admin.vehiculos.tipos.destroy', $tipo) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este tipo de vehículo?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-xs"
                                                    style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="fas fa-shapes fa-2x d-block mb-2"></i>
                                No hay tipos de vehículo registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tiposVehiculo->hasPages())
            <div class="card-footer">
                {{ $tiposVehiculo->links() }}
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