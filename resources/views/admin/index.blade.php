@extends('layouts.app')

@section('subtitle', 'Panel Principal')
@section('content_header_title', 'Inicio')
@section('content_header_subtitle', 'Dashboard de Operaciones')

@section('content_body')
<div class="container-fluid">
    
    {{-- 1. FILA DE TARJETAS ESTADÍSTICAS (SMALL BOXES) --}}
    <div class="row">
        <!-- Tarjeta: Guardia -->
        <div class="col-lg-3 col-6">
            <div class="small-box {{ $guardiaHoy ? 'bg-success' : 'bg-secondary' }}">
                <div class="inner">
                    <h3>{{ $guardiaHoy ? 'Activa' : 'Sin Abrir' }}</h3>
                    <p>{{ $guardiaHoy ? 'Oficial: ' . $guardiaHoy->oficial?->name : 'No se ha iniciado la guardia' }}</p>
                </div>
                <div class="icon"><i class="fas fa-shield-alt"></i></div>
                <a href="{{ route('admin.guardias.index') }}" class="small-box-footer">Ver Guardias <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Tarjeta: Flota en Ruta -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $vehiculosEnRuta }}</h3>
                    <p>Vehículos en Misión</p>
                </div>
                <div class="icon"><i class="fas fa-shipping-fast"></i></div>
                <a href="{{ route('admin.guardias.index') }}" class="small-box-footer">Ver Libro de Guardia <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Tarjeta: Conductores Activos -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalConductores }}</h3>
                    <p>Conductores Habilitados</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
                <a href="{{ route('admin.conductores.index') }}" class="small-box-footer">Personal Logístico <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Tarjeta: Vuelos del Día -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner text-white">
                    <h3>{{ $vuelosActivos }}</h3>
                    <p>Vuelos Programados Hoy</p>
                </div>
                <div class="icon"><i class="fas fa-dove"></i></div>
                <a href="{{ route('admin.vuelos.index') }}" class="small-box-footer style="color: rgba(255,255,255,0.8) !important"">Módulo Palomar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- 2. DISTRIBUCIÓN DE PANELES CENTRALES --}}
    <div class="row">
        
        {{-- COLUMNA IZQUIERDA (ANCHEZA: col-md-7) --}}
        <div class="col-md-7">
            <!-- Salidas de Vehículos -->
            <div class="card card-outline card-primary">
                <div class="card-header border-transparent">
                    <h3 class="card-title"><i class="fas fa-truck-moving mr-1"></i> Últimas Salidas de Vehículos</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0 table-hover text-sm">
                            <thead>
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Conductor</th>
                                    <th>Salida</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimasSalidas as $salida)
                                <tr>
                                    <td><strong>{{ $salida->vehiculo?->matricula }}</strong></td>
                                    <td>{{ $salida->conductor?->nombre_corto }}</td>
                                    <td>{{ $salida->hora_sale }}</td>
                                    <td>
                                        @if($salida->hora_entra)
                                            <span class="badge badge-success">Retornado</span>
                                        @else
                                            <span class="badge badge-warning">En Ruta</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No hay salidas registradas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Novedades de la Guardia -->
            <div class="card card-outline card-dark">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history mr-1"></i> Diario de Novedades (Últimas)</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="products-list product-list-in-card pl-2 pr-2">
                        @forelse($ultimasNovedades as $novedad)
                        <li class="item">
                            <div class="product-info ml-2">
                                <span class="product-title">
                                    N° {{ $novedad->number }} — Presidencia/Organismo
                                    <span class="badge badge-info float-right">{{ $novedad->clasification }}</span>
                                </span>
                                <span class="product-description text-xs text-muted">
                                    <strong>{{ $novedad->time }} hs</strong> - {{ Str::limit($novedad->text, 100) }}
                                </span>
                            </div>
                        </li>
                        @empty
                        <li class="item text-center text-muted py-3">Sin novedades en el libro.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA (ESTRECHA: col-md-5) --}}
        <div class="col-md-5">
            <!-- Alertas Documentación -->
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-circle mr-1"></i> Control de Vencimientos</h3>
                </div>
                <div class="card-body p-2">
                    @forelse($conductoresAlertas as $con)
                        <div class="callout callout-danger py-2 mb-2">
                            <h6 class="font-weight-bold mb-1">{{ $con->nombre_corto }}</h6>
                            <small class="text-muted d-block">Revisar documentación médica o licencias de conducir pronto.</small>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-check-circle text-success mb-1 d-block" style="font-size: 1.5rem"></i> Todo al día.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Resumen de Vuelos Colombofilia -->
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-dove mr-1"></i> Monitoreo de Vuelos</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table m-0 table-striped text-xs">
                        <thead>
                            <tr>
                                <th>Punto Suelta</th>
                                <th>Palomas</th>
                                <th>Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosVuelos as $vuelo)
                            <tr>
                                <td><strong>{{ $vuelo->punto_liberacion ?? 'Entrenamiento' }}</strong></td>
                                <td><span class="badge badge-secondary">{{ $vuelo->palomas_count }}</span></td>
                                <td>{{ $vuelo->hora_liberacion ?? '--:--' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No hay vuelos recientes.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@stop