@extends('layouts.app')

@section('subtitle', 'Unidad: ' . $unidad->nombre)
@section('content_header_title', 'Unidades')
@section('content_header_subtitle', 'Detalle')

@section('content_body')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-building text-primary"></i> {{ $unidad->nombre }}
                @if($unidad->activo)
                    <span class="badge badge-success">Activo</span>
                @else
                    <span class="badge badge-secondary">Inactivo</span>
                @endif
            </h3>
            <div class="card-tools">
                @can('update', $unidad)
                    <a href="{{ route('admin.unidades.edit', $unidad) }}" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                @endcan
                <a href="{{ route('admin.unidades.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Vehículos asignados</strong>
                    <p class="text-muted">{{ $unidad->vehiculos()->count() }} vehículos</p>
                </div>
            </div>

            @if($unidad->vehiculos->isNotEmpty())
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Matrícula</th>
                            <th>Marca/Modelo</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unidad->vehiculos as $v)
                            <tr>
                                <td><strong>{{ $v->matricula }}</strong></td>
                                <td>{{ trim("{$v->marca} {$v->modelo}") ?: '-' }}</td>
                                <td>{{ $v->tipoVehiculo->nombre ?? '-' }}</td>
                                <td>
                                    <span class="{{ $v->estado_badge_class }}">{{ $v->estado_label }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted text-center">No hay vehículos asignados a esta unidad.</p>
            @endif
        </div>
    </div>
</div>
@stop
