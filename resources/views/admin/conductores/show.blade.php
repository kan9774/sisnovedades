@extends('layouts.app')

@section('subtitle', 'Detalle del Conductor')
@section('content_header_title', 'Conductores')
@section('content_header_subtitle', 'Detalle')

@section('content_body')
<div class="container-fluid">
    <div class="row">
        {{-- Tarjeta Perfil Rápido --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-tie fa-4x text-secondary"></i>
                    </div>

                    <h3 class="profile-username text-center">{{ $conductor->nombre_completo }}</h3>
                    <p class="text-muted text-center">{{ $conductor->grado }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Documento (Cédula)</b> <a class="float-right text-dark font-weight-bold">{{ $conductor->documento }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Estado</b> 
                            <span class="float-right badge {{ $conductor->activo ? 'badge-success' : 'badge-secondary' }}">
                                {{ $conductor->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Licencia Conducir</b> 
                            <span class="float-right badge {{ $conductor->licencia_vigente ? 'badge-success' : 'badge-danger' }}">
                                {{ $conductor->categoria_licencia }} - {{ $conductor->nro_licencia }}
                            </span>
                        </li>
                    </ul>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.conductores.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        @can('update', $conductor)
                            <a href="{{ route('admin.conductores.edit', $conductor) }}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Tarjeta de Observaciones --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-comment-alt mr-1"></i> Observaciones</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        {{ $conductor->observaciones ?? 'Sin observaciones registradas.' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Detalles de Documentación y Novedades --}}
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#documentacion" data-toggle="tab"><i class="fas fa-file-medical mr-1"></i> Documentación Vencimientos</a></li>
                        <li class="nav-item"><a class="nav-link" href="#historial" data-toggle="tab"><i class="fas fa-history mr-1"></i> Últimas Salidas / Novedades</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        
                        {{-- Tab Documentación --}}
                        <div class="active tab-pane" id="documentacion">
                            <div class="row">
                                {{-- Licencia --}}
                                <div class="col-sm-4">
                                    <div class="position-relative p-3 bg-light" style="height: 120px; border-radius: 5px; border-left: 5px solid #007bff;">
                                        <div class="ribbon-wrapper">
                                            <div class="ribbon {{ $conductor->licencia_vigente ? 'bg-success' : 'bg-danger' }} text-xs">
                                                {{ $conductor->licencia_vigente ? 'Vigente' : 'Vencido' }}
                                            </div>
                                        </div>
                                        <span class="font-weight-bold d-block text-primary">Licencia de Conducir</span>
                                        <small class="text-muted d-block">Categoría: {{ $conductor->categoria_licencia }}</small>
                                        <small class="text-muted d-block">Nº: {{ $conductor->nro_licencia }}</small>
                                        <span class="text-sm font-weight-bold">Vence: {{ $conductor->fecha_vencimiento_licencia->format('d/m/Y') }}</span>
                                    </div>
                                </div>

                                {{-- Carné de Salud --}}
                                <div class="col-sm-4">
                                    @if($conductor->fecha_vencimiento_carne_salud)
                                        <div class="position-relative p-3 bg-light" style="height: 120px; border-radius: 5px; border-left: 5px solid #28a745;">
                                            <div class="ribbon-wrapper">
                                                <div class="ribbon {{ $conductor->carne_salud_vigente ? 'bg-success' : 'bg-danger' }} text-xs">
                                                    {{ $conductor->carne_salud_vigente ? 'Vigente' : 'Vencido' }}
                                                </div>
                                            </div>
                                            <span class="font-weight-bold d-block text-success">Carné de Salud</span>
                                            <small class="text-muted d-block">Lugar: {{ $conductor->lugar_carne_salud ?? 'No especificado' }}</small>
                                            <span class="text-sm font-weight-bold d-block mt-3">Vence: {{ $conductor->fecha_vencimiento_carne_salud->format('d/m/Y') }}</span>
                                        </div>
                                    @else
                                        <div class="p-3 bg-light d-flex align-items-center justify-content-center text-muted" style="height: 120px; border-radius: 5px; border-left: 5px solid #6c757d;">
                                            <span>Sin Carné de Salud</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Carné Habilitante --}}
                                <div class="col-sm-4">
                                    @if($conductor->fecha_vencimiento_carne_habilitante)
                                        <div class="position-relative p-3 bg-light" style="height: 120px; border-radius: 5px; border-left: 5px solid #17a2b8;">
                                            <div class="ribbon-wrapper">
                                                <div class="ribbon {{ $conductor->carne_habilitante_vigente ? 'bg-success' : 'bg-danger' }} text-xs">
                                                    {{ $conductor->carne_habilitante_vigente ? 'Vigente' : 'Vencido' }}
                                                </div>
                                            </div>
                                            <span class="font-weight-bold d-block text-info">Carné Habilitante</span>
                                            <small class="text-muted d-block">Habilitado: {{ $conductor->tipo_vehiculo_habilitado ?? 'General' }}</small>
                                            <span class="text-sm font-weight-bold d-block mt-2">Vence: {{ $conductor->fecha_vencimiento_carne_habilitante->format('d/m/Y') }}</span>
                                        </div>
                                    @else
                                        <div class="p-3 bg-light d-flex align-items-center justify-content-center text-muted" style="height: 120px; border-radius: 5px; border-left: 5px solid #6c757d;">
                                            <span>Sin Carné Habilitante</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Tab Historial de Salidas --}}
                        <div class="tab-pane" id="historial">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha/Hora</th>
                                        <th>Vehículo</th>
                                        <th>Novedad / Destino</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Se asume que novedadesVehiculos tiene campos comunes como fecha, vehiculo y detalle --}}
                                    @forelse($conductor->novedadesVehiculos as $novedad)
                                        <tr>
                                            <td>{{ $novedad->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $novedad->vehiculo?->linea_o_patente ?? 'N/A' }}</td>
                                            <td>{{ Str::limit($novedad->descripcion ?? $novedad->observacion, 50) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No se registran salidas recientes para este conductor.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop