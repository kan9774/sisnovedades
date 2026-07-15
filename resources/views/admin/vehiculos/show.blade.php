@extends('layouts.app')

@section('subtitle', 'Vehículo: ' . $vehiculo->matricula)
@section('content_header_title', 'Vehículos')
@section('content_header_subtitle', 'Detalle')

@section('content_body')
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck text-primary"></i> {{ $vehiculo->matricula }}
                    @if ($vehiculo->activo)
                        <span class="badge badge-success">Activo</span>
                    @else
                        <span class="badge badge-secondary">Inactivo</span>
                    @endif
                    <span class="{{ $vehiculo->estado_badge_class }}">{{ $vehiculo->estado_label }}</span>
                </h3>
                <div class="card-tools">
                    @can('update', $vehiculo)
                        <a href="{{ route('admin.vehiculos.edit', $vehiculo) }}" class="btn btn-outline-warning btn-sm"
                            style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    @endcan
                    <a href="{{ route('admin.vehiculos.index') }}" class="btn btn-outline-secondary btn-sm"
                        style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Marca / Modelo</strong>
                        <p class="text-muted">
                            {{ trim("{$vehiculo->marca} {$vehiculo->modelo}") ?: '-' }}
                        </p>
                    </div>
                    <div class="col-md-2">
                        <strong>Tipo</strong>
                        <p class="text-muted">{{ $vehiculo->tipoVehiculo->nombre ?? '-' }}</p>
                    </div>
                    <div class="col-md-2">
                        <strong>Vehículo</strong>
                        <p class="text-muted">{{ $vehiculo->vehiculo ?? '-' }}</p>
                    </div>
                    <div class="col-md-2">
                        <strong>Ejes</strong>
                        <p class="text-muted">{{ $vehiculo->ejes ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Combustible</strong>
                        <p class="text-muted">{{ $vehiculo->tipoCombustible->nombre ?? '-' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Lubricante</strong>
                        <p class="text-muted">{{ $vehiculo->tipoLubricante->nombre ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Rodado</strong>
                        <p class="text-muted">
                            {{ $vehiculo->tipoRodado->nombre ?? '-' }}
                            @if ($vehiculo->tipoRodado?->presion_recomendada)
                                <span class="badge badge-light border">{{ $vehiculo->tipoRodado->presion_recomendada }}
                                    PSI</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-3">
                        <strong>Odómetro</strong>
                        <p class="text-muted">
                            @if ($vehiculo->sin_cuentakilometros)
                                <span class="badge badge-danger">S/ODO</span>
                            @else
                                <span class="badge badge-success">C/ODO</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-3">
                        <strong>Consumo (L/km)</strong>
                        <p class="text-muted">
                            {{ $vehiculo->consumo_litros_por_km ? number_format($vehiculo->consumo_litros_por_km, 2, ',', '.') : '-' }}
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <strong>N° Chasis</strong>
                        <p class="text-muted">{{ $vehiculo->numero_chasis ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>N° Motor</strong>
                        <p class="text-muted">{{ $vehiculo->numero_motor ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Descripción</strong>
                        <p class="text-muted">{{ $vehiculo->descripcion ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        @livewire('vehiculos.mantenimiento-modal', ['vehiculo' => $vehiculo])

        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-route text-secondary"></i> Últimas Salidas
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Guardia</th>
                            <th>Conductor</th>
                            <th>Hora sale</th>
                            <th>Hora entra</th>
                            <th>Kms recorridos</th>
                            <th>Litros</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehiculo->salidas as $salida)
                            <tr>
                                <td>{{ $salida->guardia ? '#' . $salida->guardia->id : '-' }}</td>
                                <td>{{ $salida->conductor ? $salida->conductor->nombre_visible : '-' }}</td>
                                <td>{{ $salida->hora_sale ? $salida->hora_sale->format('H:i') : '-' }}</td>
                                <td>{{ $salida->hora_entra ? $salida->hora_entra->format('H:i') : '-' }}</td>
                                <td>{{ $salida->kms_recorridos ?? '-' }}</td>
                                <td>{{ $salida->litros ? number_format($salida->litros, 2, ',', '.') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No hay salidas registradas para este vehículo.
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
