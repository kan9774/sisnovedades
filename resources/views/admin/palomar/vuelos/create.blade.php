@extends('layouts.app')

@section('subtitle', 'Registrar Vuelo')
@section('content_header_title', 'Vuelos')
@section('content_header_subtitle', 'Crear')

@section('content_body')
    <div class="container-fluid">
        <x-ops-card title="Registrar Vuelo" icon="plane" eyebrow="Nuevo Registro">
            <form action="{{ route('admin.vuelos.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="fecha">Fecha <span class="text-danger">*</span></label>
                        <input type="date" name="fecha" id="fecha"
                            class="form-control @error('fecha') is-invalid @enderror"
                            value="{{ old('fecha', date('Y-m-d')) }}" required>
                        @error('fecha')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="tipo">Tipo <span class="text-danger">*</span></label>
                        <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror"
                            required>
                            <option value="entrenamiento" {{ old('tipo') == 'entrenamiento' ? 'selected' : '' }}>
                                Entrenamiento</option>
                            <option value="competicion" {{ old('tipo') == 'competicion' ? 'selected' : '' }}>Competición
                            </option>
                        </select>
                        @error('tipo')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="hora_liberacion">Hora de liberación</label>
                        <input type="time" name="hora_liberacion" id="hora_liberacion" class="form-control"
                            value="{{ old('hora_liberacion') }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="punto_liberacion">Punto de liberación</label>
                        <input type="text" name="punto_liberacion" id="punto_liberacion" class="form-control"
                            value="{{ old('punto_liberacion') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="condiciones_climaticas">Condiciones climáticas</label>
                    <textarea name="condiciones_climaticas" id="condiciones_climaticas" class="form-control" rows="2">{{ old('condiciones_climaticas') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="observaciones">Observaciones generales del vuelo</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
                </div>

                <hr>
                <h5 class="my-3 text-dark"><i class="fas fa-dove mr-1"></i> Palomas participantes <span
                        class="text-danger">*</span></h5>
                @error('palomas')
                    <div class="alert alert-danger py-2">{{ $message }}</div>
                @enderror

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
                                    $preseleccionadas = old(
                                        'palomas',
                                        $palomaIdPreseleccionada ? [$palomaIdPreseleccionada] : [],
                                    );
                                    $checked = in_array($paloma->id, $preseleccionadas);
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="paloma-check" name="palomas[]"
                                            value="{{ $paloma->id }}" {{ $checked ? 'checked' : '' }}>
                                    </td>
                                    <td>{{ $paloma->anilla }}</td>
                                    <td>{{ $paloma->nombre ?? '-' }}</td>
                                    <td><span
                                            class="badge-ops badge-ops-secondary">{{ $paloma->estado->nombre ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <input type="text" name="datos[{{ $paloma->id }}][anilla_competicion]"
                                            class="form-control form-control-sm paloma-datos"
                                            value="{{ old("datos.{$paloma->id}.anilla_competicion") }}"
                                            {{ $checked ? '' : 'disabled' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <small class="text-muted d-block mb-3">Distancia, hora de llegada y posición se cargan al finalizar el
                    vuelo, desde "Cargar resultados".</small>

                <div class="mt-4">
                    <div class="mt-4">
                        <x-btn-ops type="submit" icon="save">Guardar</x-btn-ops>
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
