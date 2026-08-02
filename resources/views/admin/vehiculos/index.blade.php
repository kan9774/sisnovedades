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

        <div class="card card-outline-ops">
            <div class="card-header-ops">
                <div class="card-header-ops__title-wrap">
                    <h3 class="card-title-ops mb-0">
                        <i class="fas fa-truck"></i> Vehículos
                    </h3>
                </div>
                <div class="card-tools">
                    @can('viewAny', App\Models\TipoVehiculo::class)
                        <a href="{{ route('admin.vehiculos.tipos.index') }}" class="btn-ops btn-ops-secondary btn-sm">
                            <i class="fas fa-shapes"></i> Tipos de Vehículo
                        </a>
                    @endcan
                    @can('viewAny', App\Models\Vehiculo::class)
                        <a href="{{ route('admin.vehiculos.export') }}" class="btn-ops btn-ops-success btn-sm"
                            aria-label="Descargar Excel">
                            <i class="fas fa-file-excel"></i> Descargar Excel
                        </a>
                    @endcan
                    @can('create', App\Models\Vehiculo::class)
                        <a href="{{ route('admin.vehiculos.create') }}" class="btn-ops btn-ops-primary btn-sm"
                            aria-label="Crear nuevo vehículo">
                            <i class="fas fa-plus-circle"></i> Nuevo Vehículo
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-ops">
                            <tr>
                                <th>Matrícula</th>
                                <th>Tipo</th>
                                <th>Vehículo</th>
                                <th>Combustible</th>
                                <th>Lubricante</th>
                                <th>Rodado</th>
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
                                    <td>{{ $vehiculo->tipoVehiculo->nombre ?? '-' }}</td>
                                    <td>{{ $vehiculo->vehiculo ?? '-' }}</td>
                                    <td>{{ $vehiculo->tipoCombustible->nombre ?? '-' }}</td>
                                    <td>{{ $vehiculo->tipoLubricante->nombre ?? '-' }}</td>
                                    <td>{{ $vehiculo->tipoRodado->nombre ?? '-' }}</td>
                                    <td>{{ $vehiculo->consumo_litros_por_km ? number_format($vehiculo->consumo_litros_por_km, 2, ',', '.') : '-' }}
                                    </td>
                                    <td>
                                        @if ($vehiculo->sin_cuentakilometros)
                                            <span class="badge-ops badge-ops-danger">S/ODO</span>
                                        @else
                                            <span class="badge-ops badge-ops-success">C/ODO</span>
                                        @endif
                                    </td>
                                    <td>{{ $vehiculo->descripcion ?? '-' }}</td>
                                    <td>
                                        @if ($vehiculo->activo)
                                            <span class="badge-ops badge-ops-success">Activo</span>
                                        @else
                                            <span class="badge-ops badge-ops-secondary">Inactivo</span>
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
                                                    class="btn-ops btn-ops-info btn-xs mr-1"
                                                    aria-label="Ver vehículo">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan
                                            @can('update', $vehiculo)
                                                <a href="{{ route('admin.vehiculos.edit', $vehiculo) }}"
                                                    class="btn-ops btn-ops-warning btn-xs mr-1"
                                                    aria-label="Editar vehículo">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $vehiculo)
                                                <form action="{{ route('admin.vehiculos.destroy', $vehiculo) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('¿Eliminar este vehículo?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-ops btn-ops-danger btn-xs"
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
                                    <td colspan="12" class="text-center text-muted py-4">
                                        <i class="fas fa-truck fa-2x d-block mb-2"></i>
                                        No hay vehículos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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