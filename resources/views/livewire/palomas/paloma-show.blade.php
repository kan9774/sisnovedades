@extends('layouts.app')

@section('subtitle', 'Paloma: ' . $paloma->anilla)
@section('content_header_title', 'Palomas')
@section('content_header_subtitle', $paloma->anilla)

@section('content_body')
<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        {{-- Columna izquierda: Datos de la paloma e historial --}}
        <div class="col-lg-4 col-md-5 mb-4">
            {{-- Card: Datos de la paloma --}}
            <x-ops-card title="Datos de la Paloma" icon="dove" eyebrow="Información">
                <x-slot:actions>
                    <a href="{{ route('admin.palomas.index') }}" class="btn-ops btn-ops-secondary btn-ops-icon" title="Volver al listado">
                        <i class="fas fa-list"></i>
                    </a>
                    <a href="{{ route('admin.palomares.index') }}" class="btn-ops btn-ops-secondary btn-ops-icon" title="Volver al palomar">
                        <i class="fas fa-home"></i>
                    </a>
                    {{-- @can('update', $paloma)
                    <a href="{{ route('admin.palomas.edit', $paloma->id) }}" class="btn-ops btn-ops-warning btn-ops-icon" title="Editar paloma">
                        <i class="fas fa-edit"></i>
                    </a>
                    @endcan --}}
                </x-slot:actions>

                <div class="row g-2">
                    {{-- Anilla --}}
                    <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                        <div class="col-6 col-sm-4 text-muted"><i class="fas fa-hashtag mr-1"></i> Anilla:</div>
                        <div class="col-6 col-sm-8 font-weight-bold">{{ $paloma->anilla }}</div>
                    </div>
                    {{-- Palomar --}}
                    <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                        <div class="col-6 col-sm-4 text-muted"><i class="fas fa-home mr-1"></i> Palomar:</div>
                        <div class="col-6 col-sm-8">
                            <a href="{{ route('admin.palomares.index') }}" style="color: #0d6efd; text-decoration: none;">
                                {{ $paloma->palomar->nombre ?? '-' }}
                            </a>
                        </div>
                    </div>
                    {{-- Sexo --}}
                    <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                        <div class="col-6 col-sm-4 text-muted"><i class="fas fa-venus-mars mr-1"></i> Sexo:</div>
                        <div class="col-6 col-sm-8">
                            @if ($paloma->sexo === 'macho')
                                <span class="badge-ops badge-ops-primary"><i class="fas fa-mars mr-1"></i> Macho</span>
                            @elseif($paloma->sexo === 'hembra')
                                <span class="badge-ops badge-ops-danger"><i class="fas fa-venus mr-1"></i> Hembra</span>
                            @else
                                <span class="badge-ops badge-ops-secondary">Desconocido</span>
                            @endif
                        </div>
                    </div>
                    {{-- Fecha Nacimiento --}}
                    <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                        <div class="col-6 col-sm-4 text-muted"><i class="fas fa-calendar-alt mr-1"></i> Fecha Nac.:</div>
                        <div class="col-6 col-sm-8">{{ optional($paloma->fecha_nacimiento)->format('d/m/Y') ?? '-' }}</div>
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
                                <span class="badge-ops badge-ops-info">
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
                        <div class="col-6 col-sm-8">
                            @if ($paloma->estado)
                                <span class="badge" style="background-color: {{ $paloma->estado->color ?? '#db1714' }}; color: #062d8a; padding: 5px 12px;">
                                    {{ $paloma->estado->nombre }}
                                </span>
                            @else
                                <span class="text-muted">Sin estado</span>
                            @endif
                        </div>
                    </div>
                    {{-- Estado Sanitario --}}
                    <div class="col-12 d-flex flex-wrap py-1 border-bottom">
                        <div class="col-6 col-sm-4 text-muted"><i class="fas fa-heartbeat mr-1"></i> Estado Sanitario:</div>
                        <div class="col-6 col-sm-8">
                            @if ($paloma->estado_sanitario === 'Bien')
                                <span class="badge-ops badge-ops-success"><i class="fas fa-check-circle mr-1"></i> Bien</span>
                            @elseif($paloma->estado_sanitario === 'Enferma')
                                <span class="badge-ops badge-ops-danger"><i class="fas fa-times-circle mr-1"></i> Enferma</span>
                            @else
                                <span class="badge-ops badge-ops-secondary">{{ $paloma->estado_sanitario ?? '-' }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Observaciones --}}
                    @if ($paloma->observaciones)
                        <div class="col-12 d-flex flex-wrap py-1">
                            <div class="col-6 col-sm-4 text-muted"><i class="fas fa-comment mr-1"></i> Observaciones:</div>
                            <div class="col-6 col-sm-8">{{ $paloma->observaciones }}</div>
                        </div>
                    @endif
                </div>
            </x-ops-card>

            {{-- Card: Historial de cambios --}}
            <x-ops-card title="Historial de cambios" icon="history" eyebrow="Registros" collapsed>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-ops">
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
                                        <span class="badge-ops badge-ops-light">
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
                                                'venta' => 'fa-shopping-cart text-info',
                                                'prestamo' => 'fa-hand-holding text-primary',
                                                'muerte' => 'fa-skull text-danger',
                                                'ausente' => 'fa-search text-warning',
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
            </x-ops-card>
        </div>

        {{-- Columna derecha: Vuelos --}}
        <div class="col-lg-8 col-md-7">
            <x-ops-card title="Vuelos registrados" icon="plane" eyebrow="Actividad" :eyebrowCount="$paloma->vuelos->count()">
                <x-slot:actions>
                    <a href="{{ route('admin.vuelos.index') }}?paloma_id={{ $paloma->id }}" wire:navigate class="btn-ops btn-ops-success btn-sm">
                        <i class="fas fa-plus-circle"></i> Registrar Vuelo
                    </a>
                </x-slot:actions>

                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 table-sm">
                        <thead class="thead-ops">
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Dist. (km)</th>
                                <th>Hora Sale</th>
                                <th>Hora Llega</th>
                                <th>Tiempo</th>
                                <th>Velocidad</th>
                                <th>Pos.</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paloma->vuelos->sortByDesc('fecha') as $vuelo)
                                <tr>
                                    <td class="text-nowrap">
                                        <span class="badge-ops badge-ops-light">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            {{ $vuelo->fecha->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($vuelo->tipo === 'entrenamiento')
                                            <span class="badge-ops badge-ops-info"><i class="fas fa-running mr-1"></i> Entrenamiento</span>
                                        @elseif($vuelo->tipo === 'competicion')
                                            <span class="badge-ops badge-ops-warning text-dark"><i class="fas fa-trophy mr-1"></i> Competición</span>
                                        @else
                                            <span class="badge-ops badge-ops-secondary">{{ ucfirst($vuelo->tipo) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $vuelo->pivot->distancia_km ?? '-' }}</td>
                                    <td>{{ optional($vuelo->hora_liberacion)->format('H:i') ?? '-' }}</td>
                                    <td>{{ optional($vuelo->pivot->hora_llegada)->format('H:i') ?? '-' }}</td>
                                    <td>
                                        @if ($vuelo->pivot->tiempo_vuelo)
                                            <span class="badge-ops badge-ops-light">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ \Carbon\Carbon::parse($vuelo->pivot->tiempo_vuelo)->format('H:i') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($vuelo->pivot->velocidad_media)
                                            <span class="badge-ops badge-ops-primary">{{ number_format($vuelo->pivot->velocidad_media, 2) }} m/min</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $vuelo->pivot->posicion ?? '-' }}</td>
                                    <td class="text-center align-middle">
                                        <div class="ops-actions">
                                            <a href="{{ route('admin.vuelos.index') }}?vuelo_edit_id={{ $vuelo->id }}" wire:navigate class="btn-ops btn-ops-warning btn-ops-icon" title="Editar vuelo">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.vuelos.destroy', $vuelo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este vuelo?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-ops btn-ops-danger btn-ops-icon" title="Eliminar vuelo">
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
            </x-ops-card>
        </div>
    </div>
</div>
@stop
