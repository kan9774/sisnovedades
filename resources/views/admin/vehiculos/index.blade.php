@extends('layouts.app')

@section('subtitle', 'Vehículos')
@section('content_header_title', 'Vehículos')
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
                <h3 class="card-title">
                    <i class="fas fa-truck"></i> Vehículos
                </h3>
                <div class="card-tools">
                    @can('create', App\Models\Vehiculo::class)
                        <a href="{{ route('admin.vehiculos.create') }}" class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);"
                            aria-label="Crear nuevo vehículo">
                            <i class="fas fa-plus-circle"></i> Nuevo Vehículo
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Matrícula</th>
                            <th>Combustible</th>
                            <th>Consumo (L/km)</th>
                            <th>Odómetro</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Clasificación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehiculos as $vehiculo)
                            <tr>
                                <td class="disabled"><strong>{{ $vehiculo->matricula }}</strong></td>
                                <td>
                                    @if ($vehiculo->tipo_combustible === 'gas_oil')
                                        <span class="badge badge-warning">Gas Oil</span>
                                    @else
                                        <span class="badge badge-info">Nafta</span>
                                    @endif
                                </td>
                                <td>{{ $vehiculo->consumo_litros_por_km ? number_format($vehiculo->consumo_litros_por_km, 2, ',', '.') : '-' }}
                                </td>
                                <td>
                                    @if ($vehiculo->sin_cuentakilometros)
                                        <span class="badge badge-danger">S/ODO</span>
                                    @else
                                        <span class="badge badge-success">C/ODO</span>
                                    @endif
                                </td>
                                <td>{{ $vehiculo->descripcion ?? '-' }}</td>
                                <td>
                                    @if ($vehiculo->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $vehiculo->estado_badge_class }}">
                                        {{ $vehiculo->estado_label }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex justify-content-center">
                                        @can('view', $vehiculo)
                                            <a href="{{ route('admin.vehiculos.show', $vehiculo) }}"
                                                class="btn btn-outline-info btn-xs mr-1"
                                                style="background-color: rgba(23, 162, 184, 0.08); border-color: rgba(23, 162, 184, 0.25);"
                                                aria-label="Ver vehículo">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan
                                        @can('update', $vehiculo)
                                            <a href="{{ route('admin.vehiculos.edit', $vehiculo) }}"
                                                class="btn btn-outline-warning btn-xs mr-1"
                                                style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);"
                                                aria-label="Editar vehículo">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('delete', $vehiculo)
                                            <form action="{{ route('admin.vehiculos.destroy', $vehiculo) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('¿Eliminar este vehículo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger btn-xs"
                                                    style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                                    aria-label="Eliminar vehículo">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-truck fa-2x d-block mb-2"></i>
                                    No hay vehículos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($vehiculos->hasPages())
                <div class="card-footer">
                    {{ $vehiculos->links() }}
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
