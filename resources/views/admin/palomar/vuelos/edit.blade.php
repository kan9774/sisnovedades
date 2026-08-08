@extends('layouts.app')

@section('subtitle', 'Editar Vuelo')
@section('content_header_title', 'Vuelos')
@section('content_header_subtitle', 'Editar')

@section('content_body')
    <div class="container-fluid">
        <x-ops-card title="Editar Vuelo" icon="plane" eyebrow="Modificación de registro">
            @if ($vuelo->estado === 'finalizado')
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Este vuelo ya fue finalizado. Podés editar los datos generales, pero la lista de palomas
                    participantes y sus anillas de competición ya no se pueden modificar.
                </div>
            @endif

            <form action="{{ route('admin.vuelos.update', $vuelo) }}" method="POST">
                @csrf @method('PUT')

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="fecha">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" id="fecha"
                            class="form-control @error('fecha') is-invalid @enderror"
                            value="{{ old('fecha', $vuelo->fecha->format('Y-m-d')) }}" required>
                        @error('fecha')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="tipo">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror"
                            required>
                            <option value="entrenamiento"
                                {{ old('tipo', $vuelo->tipo) == 'entrenamiento' ? 'selected' : '' }}>Entrenamiento</option>
                            <option value="competicion" {{ old('tipo', $vuelo->tipo) == 'competicion' ? 'selected' : '' }}>
                                Competición</option>
                        </select>
                        @error('tipo')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="hora_liberacion">Hora de liberación</label>
                        <input type="time" name="hora_liberacion" id="hora_liberacion" class="form-control"
                            value="{{ old('hora_liberacion', optional($vuelo->hora_liberacion)->format('H:i')) }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="punto_liberacion">Punto de liberación</label>
                        <input type="text" name="punto_liberacion" id="punto_liberacion" class="form-control"
                            value="{{ old('punto_liberacion', $vuelo->punto_liberacion) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="condiciones_climaticas">Condiciones climáticas</label>
                    <textarea name="condiciones_climaticas" id="condiciones_climaticas" class="form-control" rows="2">{{ old('condiciones_climaticas', $vuelo->condiciones_climaticas) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="observaciones">Observaciones generales del vuelo</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="2">{{ old('observaciones', $vuelo->observaciones) }}</textarea>
                </div>

                <hr>
                <h5 class="my-3 text-dark"><i class="fas fa-dove mr-1"></i> Palomas participantes <span
                        class="text-danger">*</span></h5>
                @error('palomas')
                    <div class="alert alert-danger py-2">{{ $message }}</div>
                @enderror

                @php $palomasVuelo = $vuelo->palomas->keyBy('id'); @endphp

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle table-ops-hover">
                        <thead class="thead-ops">
                            <tr>
                                <th style="width:40px;"></th>
                                <th>Anilla</th>
                                <th>Nombre</th>
                                <th>Estado actual</th>
                                <th>Anilla de competición</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($palomas as $paloma)
                                @php
                                    $pivotExistente = $palomasVuelo->get($paloma->id)?->pivot;
                                    $checked = in_array($paloma->id, old('palomas', $palomasVuelo->keys()->toArray()));
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="paloma-check" name="palomas[]"
                                            value="{{ $paloma->id }}" {{ $checked ? 'checked' : '' }}
                                            {{ $vuelo->estado === 'finalizado' ? 'disabled' : '' }}>
                                        @if ($vuelo->estado === 'finalizado' && $checked)
                                            <input type="hidden" name="palomas[]" value="{{ $paloma->id }}">
                                        @endif
                                    </td>
                                    <td>{{ $paloma->anilla }}</td>
                                    <td>{{ $paloma->nombre ?? '-' }}</td>
                                    <td><span
                                            class="badge-ops badge-ops-secondary">{{ $paloma->estado->nombre ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <input type="text" name="datos[{{ $paloma->id }}][anilla_competicion]"
                                            class="form-control form-control-sm paloma-datos"
                                            value="{{ old("datos.{$paloma->id}.anilla_competicion", $pivotExistente->anilla_competicion ?? '') }}"
                                            {{ $checked ? '' : 'disabled' }}
                                            {{ $vuelo->estado === 'finalizado' ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <div class="mt-4">
                        <x-btn-ops type="submit" icon="save">Actualizar</x-btn-ops>
                        <x-btn-ops href="{{ route('admin.vuelos.index') }}" variant="info">Cancelar</x-btn-ops>
                    </div>
                </div>
            </form>
        </x-ops-card>
    </div>
@stop

@push('js')
    <script>
        document.querySelectorAll('.paloma-check').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var row = this.closest('tr');
                row.querySelectorAll('.paloma-datos').forEach(function(input) {
                    input.disabled = !checkbox.checked;
                });
            });
        });
    </script>
@endpush
