@extends('layouts.app')

@section('subtitle', 'Cargar Resultados')
@section('content_header_title', 'Vuelos')
@section('content_header_subtitle', 'Cargar Resultados')

@section('content_body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-flag-checkered"></i> Cargar Resultados — Vuelo del {{ $vuelo->fecha->format('d/m/Y') }}</h3>
        </div>
        <div class="card-body">
            <p>
                <strong>Tipo:</strong> {{ ucfirst($vuelo->tipo) }} &nbsp;|&nbsp;
                <strong>Punto de liberación:</strong> {{ $vuelo->punto_liberacion ?? '-' }} &nbsp;|&nbsp;
                <strong>Hora de liberación:</strong> {{ optional($vuelo->hora_liberacion)->format('H:i') ?? '-' }}
            </p>

            <form action="{{ route('admin.vuelos.guardar-resultados', $vuelo) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Anilla</th>
                                <th>Nombre</th>
                                <th>Anilla competición</th>
                                <th>Distancia (km)</th>
                                <th>Hora llegada</th>
                                <th>Posición</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vuelo->palomas as $paloma)
                                <tr>
                                    <td>{{ $paloma->anilla }}</td>
                                    <td>{{ $paloma->nombre ?? '-' }}</td>
                                    <td>{{ $paloma->pivot->anilla_competicion ?? '-' }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="datos[{{ $paloma->id }}][distancia_km]" class="form-control form-control-sm" value="{{ old("datos.{$paloma->id}.distancia_km", $paloma->pivot->distancia_km) }}">
                                    </td>
                                    <td>
                                        <input type="time" name="datos[{{ $paloma->id }}][hora_llegada]" class="form-control form-control-sm" value="{{ old("datos.{$paloma->id}.hora_llegada", optional($paloma->pivot->hora_llegada)->format('H:i')) }}">
                                    </td>
                                    <td>
                                        <input type="number" min="1" name="datos[{{ $paloma->id }}][posicion]" class="form-control form-control-sm" value="{{ old("datos.{$paloma->id}.posicion", $paloma->pivot->posicion) }}">
                                    </td>
                                    <td>
                                        <input type="text" name="datos[{{ $paloma->id }}][observaciones]" class="form-control form-control-sm" value="{{ old("datos.{$paloma->id}.observaciones", $paloma->pivot->observaciones) }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-success"><i class="fas fa-flag-checkered"></i> Finalizar Vuelo y Guardar Resultados</button>
                <a href="{{ route('admin.vuelos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@stop