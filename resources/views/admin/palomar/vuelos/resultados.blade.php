@extends('layouts.app')

@section('subtitle', 'Cargar Resultados')
@section('content_header_title', 'Vuelos')
@section('content_header_subtitle', 'Cargar Resultados')

@section('content_body')
    <div class="container-fluid">
        <x-ops-card title="Cargar Resultados" icon="flag-checkered"
            titleSuffix="— Vuelo del {{ $vuelo->fecha->format('d/m/Y') }}">

            <div class="novedad-texto mb-4">
                <span class="novedad-texto__eyebrow"><i class="fas fa-info-circle"></i> Información del Vuelo</span>
                <div class="row text-dark">
                    <div class="col-md-4"><strong>Tipo:</strong> {{ ucfirst($vuelo->tipo) }}</div>
                    <div class="col-md-4"><strong>Punto de liberación:</strong> {{ $vuelo->punto_liberacion ?? '-' }}</div>
                    <div class="col-md-4"><strong>Hora de liberación:</strong>
                        {{ optional($vuelo->hora_liberacion)->format('H:i') ?? '-' }}</div>
                </div>
            </div>

            <form action="{{ route('admin.vuelos.guardar-resultados', $vuelo) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle table-ops-hover">
                        <thead class="thead-ops">
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
                            @foreach ($vuelo->palomas as $paloma)
                                <tr>
                                    <td><strong>{{ $paloma->anilla }}</strong></td>
                                    <td>{{ $paloma->nombre ?? '-' }}</td>
                                    <td>{{ $paloma->pivot->anilla_competicion ?? '-' }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            name="datos[{{ $paloma->id }}][distancia_km]"
                                            class="form-control form-control-sm"
                                            value="{{ old("datos.{$paloma->id}.distancia_km", $paloma->pivot->distancia_km) }}">
                                    </td>
                                    <td>
                                        <input type="time" name="datos[{{ $paloma->id }}][hora_llegada]"
                                            class="form-control form-control-sm"
                                            value="{{ old("datos.{$paloma->id}.hora_llegada", optional($paloma->pivot->hora_llegada)->format('H:i')) }}">
                                    </td>
                                    <td>
                                        <input type="number" min="1" name="datos[{{ $paloma->id }}][posicion]"
                                            class="form-control form-control-sm"
                                            value="{{ old("datos.{$paloma->id}.posicion", $paloma->pivot->posicion) }}">
                                    </td>
                                    <td>
                                        <input type="text" name="datos[{{ $paloma->id }}][observaciones]"
                                            class="form-control form-control-sm"
                                            value="{{ old("datos.{$paloma->id}.observaciones", $paloma->pivot->observaciones) }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <div class="mt-4">
                        <x-btn-ops type="submit" icon="flag-checkered" variant="success">Finalizar Vuelo y Guardar
                            Resultados</x-btn-ops>
                        <x-btn-ops href="{{ route('admin.vuelos.index') }}" variant="info">Cancelar</x-btn-ops>
                    </div>
                </div>
            </form>
        </x-ops-card>
    </div>
@stop
