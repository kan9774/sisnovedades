@extends('layouts.app')

@section('subtitle', 'Paloma: ' . $paloma->anilla)
@section('content_header_title', 'Palomas')
@section('content_header_subtitle', $paloma->anilla)

@section('content_body')
    <div class="container-fluid">

        {{-- Mensajes de éxito --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Mensajes de error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            {{-- Columna izquierda: Datos de la paloma e historial --}}
            <div class="col-lg-4 col-md-5 mb-4">
                {{-- Card: Datos de la paloma --}}
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #0d6efd;">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle text-primary"></i> Datos de la Paloma
                        </h5>
                        <div class="card-tools">
                            <div class="btn-group btn-group-sm mt-2 mt-sm-0" role="group">
                                {{-- Botón: Volver al listado de palomas --}}
                                <a href="{{ route('admin.palomas.index') }}" class="btn btn-outline-secondary"
                                    style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                                    aria-label="Volver al listado de palomas" title="Volver al listado">
                                    <i class="fas fa-list"></i>
                                </a>
                                {{-- Botón: Volver al palomar --}}
                                <a href="{{ route('admin.palomares.show', $paloma->palomar_id) }}"
                                    class="btn btn-outline-secondary"
                                    style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                                    aria-label="Volver al palomar" title="Volver al palomar">
                                    <i class="fas fa-home"></i>
                                </a>
                                {{-- Botón: Editar --}}
                                <a href="{{ route('admin.palomas.edit', $paloma) }}" class="btn btn-outline-warning"
                                    style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);"
                                    aria-label="Editar paloma" title="Editar paloma">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- Datos en formato lista responsiva --}}
                        <div class="row g-2">
                            {{-- Anilla --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-hashtag mr-1"></i> Anilla:</div>
                                <div class="col-6 col-sm-8 font-weight-bold">{{ $paloma->anilla }}</div>
                            </div>
                            {{-- Nombre --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-tag mr-1"></i> Nombre:</div>
                                <div class="col-6 col-sm-8">{{ $paloma->nombre ?? '-' }}</div>
                            </div>
                            {{-- Palomar --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-home mr-1"></i> Palomar:</div>
                                <div class="col-6 col-sm-8">
                                    <a href="{{ route('admin.palomares.show', $paloma->palomar_id) }}"
                                        style="color: #0d6efd; text-decoration: none;">
                                        {{ $paloma->palomar->nombre }}
                                    </a>
                                </div>
                            </div>
                            {{-- Sexo --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-venus-mars mr-1"></i> Sexo:</div>
                                <div class="col-6 col-sm-8">
                                    @if ($paloma->sexo === 'macho')
                                        <span class="badge bg-primary"><i class="fas fa-mars mr-1"></i> Macho</span>
                                    @elseif($paloma->sexo === 'hembra')
                                        <span class="badge bg-danger"><i class="fas fa-venus mr-1"></i> Hembra</span>
                                    @else
                                        <span class="badge bg-secondary">Desconocido</span>
                                    @endif
                                </div>
                            </div>
                            {{-- Color --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-palette mr-1"></i> Color:</div>
                                <div class="col-6 col-sm-8">{{ $paloma->color ?? '-' }}</div>
                            </div>
                            {{-- Raza --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-paw mr-1"></i> Raza:</div>
                                <div class="col-6 col-sm-8">{{ $paloma->raza ?? '-' }}</div>
                            </div>
                            {{-- Origen --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-globe-americas mr-1"></i> Origen:
                                </div>
                                <div class="col-6 col-sm-8">{{ $paloma->origen ?? '-' }}</div>
                            </div>
                            {{-- Fecha Nacimiento --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-calendar-alt mr-1"></i> Fecha Nac.:
                                </div>
                                <div class="col-6 col-sm-8">
                                    {{ optional($paloma->fecha_nacimiento)->format('d/m/Y') ?? '-' }}</div>
                            </div>
                            {{-- Edad --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-clock mr-1"></i> Edad:</div>
                                <div class="col-6 col-sm-8">
                                    @if ($paloma->fecha_nacimiento)
                                        @php
                                            $meses = intval($paloma->fecha_nacimiento->diffInMonths(now()));
                                            $años = floor($meses / 12);
                                            $mesesRestantes = $meses % 12;
                                        @endphp
                                        <span class="badge bg-info">
                                            @if ($años > 0)
                                                {{ $años }} año{{ $años > 1 ? 's' : '' }}
                                                @if ($mesesRestantes > 0)
                                                    y {{ $mesesRestantes }} mes{{ $mesesRestantes > 1 ? 'es' : '' }}
                                                @endif
                                            @else
                                                {{ $meses }} mes{{ $meses > 1 ? 'es' : '' }}
                                            @endif
                                        </span>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                            {{-- Estado --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-circle mr-1"></i> Estado:</div>
                                <dd class="col-sm-7 col-sm-8">

                                    @if ($paloma->estado)
                                        <span class="badge"
                                            style="background-color: {{ $paloma->estado->color ?? '#6c757d' }}; color: #fff; padding: 5px 12px;">
                                            {{ $paloma->estado->nombre }}
                                        </span>
                                    @else
                                        <span class="text-muted">Sin estado</span>
                                    @endif
                                </dd>
                            </div>
                            {{-- Estado Sanitario --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-heartbeat"></i> Estado Sanitario:</div>
                                <dd class="col-sm-7 col-sm-8">

                                    <div class="mb-2">
                                        
                                        @if ($paloma->estado_sanitario === 'Bien')
                                            <span class="badge badge-success">Bien</span>
                                        @else
                                            <span class="badge badge-danger">Enferma</span>
                                        @endif
                                    </div>
                                </dd>
                            </div>
                            {{-- Padre --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-male mr-1"></i> Padre:</div>
                                <div class="col-6 col-sm-8">
                                    @if ($paloma->padre)
                                        <a href="{{ route('admin.palomas.show', $paloma->padre_id) }}"
                                            style="color: #0d6efd; text-decoration: none;">
                                            {{ $paloma->padre->anilla }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            {{-- Madre --}}
                            <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                                <div class="col-6 col-sm-4 text-muted"><i class="fas fa-female mr-1"></i> Madre:</div>
                                <div class="col-6 col-sm-8">
                                    @if ($paloma->madre)
                                        <a href="{{ route('admin.palomas.show', $paloma->madre_id) }}"
                                            style="color: #0d6efd; text-decoration: none;">
                                            {{ $paloma->madre->anilla }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            {{-- Observaciones --}}
                            @if ($paloma->observaciones)
                                <div class="col-12 d-flex flex-wrap py-1">
                                    <div class="col-6 col-sm-4 text-muted"><i class="fas fa-comment mr-1"></i>
                                        Observaciones:</div>
                                    <div class="col-6 col-sm-8">{{ $paloma->observaciones }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card: Historial de cambios --}}
                <div class="card card-outline card-primary collapsed-card">
                    <div class="card-header" style="background: #f8f9fa;">
                        <h5 class="card-title text-secondary">
                            <i class="fas fa-history text-secondary"></i> Historial de cambios
                        </h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Evento</th>
                                        <th>Estado anterior</th>
                                        <th>Estado nuevo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($paloma->historial->sortByDesc('fecha_evento') as $h)
                                        <tr>
                                            <td class="text-nowrap">
                                                <span class="badge bg-light text-dark">
                                                    <i class="far fa-calendar-alt mr-1"></i>
                                                    {{ $h->fecha_evento->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $iconosEvento = [
                                                        'creacion' => 'fa-plus-circle text-success',
                                                        'cambio_estado' => 'fa-exchange-alt text-warning',
                                                        'traslado' => 'fa-truck text-primary',
                                                        'baja' => 'fa-times-circle text-danger',
                                                    ];
                                                @endphp
                                                <i class="fas {{ $iconosEvento[$h->evento] ?? 'fa-circle' }} mr-1"></i>
                                                {{ ucfirst($h->evento) }}
                                            </td>
                                            <td>{{ optional($h->estadoAnterior)->nombre ?? '-' }}</td>
                                            <td>{{ optional($h->estadoNuevo)->nombre ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                <i class="fas fa-info-circle mr-1"></i> Sin historial
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna derecha: Vuelos --}}
            <div class="col-lg-8 col-md-7">
                <div class="card card-outline card-success">
                    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #28a745;">
                        <h5 class="card-title">
                            <i class="fas fa-plane text-success"></i> Vuelos registrados
                            <span class="badge bg-success ml-2">{{ $paloma->vuelos->count() }}</span>
                        </h5>
                        <div class="card-tools">
                            <a href="{{ route('admin.vuelos.create', ['paloma_id' => $paloma->id]) }}"
                                class="btn btn-outline-success btn-sm mt-2 mt-sm-0"
                                style="background-color: rgba(40, 167, 69, 0.08); border-color: rgba(40, 167, 69, 0.25);"
                                aria-label="Registrar vuelo">
                                <i class="fas fa-plus-circle"></i> Registrar Vuelo
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Distancia (km)</th>
                                        <th>Hora Sale</th>
                                        <th>Hora Llega</th>
                                        <th>Tiempo</th>
                                        <th>Velocidad</th>
                                        <th>Posición</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($paloma->vuelos->sortByDesc('fecha') as $vuelo)
                                        <tr>
                                            <td class="text-nowrap">
                                                <span class="badge bg-light text-dark">
                                                    <i class="far fa-calendar-alt mr-1"></i>
                                                    {{ $vuelo->fecha->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($vuelo->tipo === 'entrenamiento')
                                                    <span class="badge bg-info"><i class="fas fa-running mr-1"></i>
                                                        Entrenamiento</span>
                                                @elseif($vuelo->tipo === 'competicion')
                                                    <span class="badge bg-warning text-dark"><i
                                                            class="fas fa-trophy mr-1"></i> Competición</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($vuelo->tipo) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $vuelo->pivot->distancia_km ?? '-' }}</td>
                                            <td>{{ optional($vuelo->hora_liberacion)->format('H:i') ?? '-' }}</td>
                                            <td>{{ optional($vuelo->pivot->hora_llegada)->format('H:i') ?? '-' }}</td>
                                            <td>
                                                @if ($vuelo->pivot->tiempo_vuelo)
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        {{ \Carbon\Carbon::parse($vuelo->pivot->tiempo_vuelo)->format('H:i') }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($vuelo->pivot->velocidad_media)
                                                    <span
                                                        class="badge bg-primary">{{ number_format($vuelo->pivot->velocidad_media, 2) }}
                                                        m/min</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $vuelo->pivot->posicion ?? '-' }}</td>
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center flex-wrap gap-1">
                                                    <a href="{{ route('admin.vuelos.edit', $vuelo) }}"
                                                        class="btn btn-outline-warning btn-xs"
                                                        style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);"
                                                        aria-label="Editar vuelo">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.vuelos.destroy', $vuelo) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('¿Eliminar este vuelo?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-outline-danger btn-xs"
                                                            style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                                            aria-label="Eliminar vuelo">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-plane fa-2x d-block mb-2" style="opacity: 0.3;"></i>
                                                No hay vuelos registrados para esta paloma.
                                            </td>
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
@stop

@push('css')
    <style>
        /* Mejoras responsivas para la vista de paloma */
        @media (max-width: 768px) {
            .card-header .btn-group {
                flex-wrap: wrap;
                gap: 4px;
            }

            .card-header .btn-group .btn {
                padding: 0.2rem 0.5rem;
                font-size: 0.75rem;
            }

            .table-responsive table {
                font-size: 0.85rem;
            }

            .table-responsive .badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }

            .d-flex .btn-xs {
                padding: 0.1rem 0.3rem;
                font-size: 0.7rem;
            }

            .card-body .row .col-5 {
                flex: 0 0 40%;
                max-width: 40%;
            }

            .card-body .row .col-7 {
                flex: 0 0 60%;
                max-width: 60%;
            }
        }

        @media (max-width: 576px) {
            .card-body .row .col-5 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 2px;
            }

            .card-body .row .col-7 {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .card-body .row .col-12 {
                padding-bottom: 0.5rem;
                border-bottom: 1px solid #e9ecef;
            }

            .card-body .row .col-12:last-child {
                border-bottom: none;
            }

            .table-responsive table {
                font-size: 0.75rem;
            }

            .table-responsive .badge {
                font-size: 0.65rem;
                padding: 0.15rem 0.4rem;
            }

            .card-header .btn {
                font-size: 0.7rem;
                padding: 0.15rem 0.4rem;
            }

            .card-header h5 {
                font-size: 1rem;
            }

            .d-flex .btn-xs {
                padding: 0.1rem 0.25rem;
                font-size: 0.6rem;
            }
        }
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            // Auto-ocultar alertas después de 5 segundos
            $('.alert').delay(5000).fadeOut('slow');
        });
    </script>
@endpush
