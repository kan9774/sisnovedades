@extends('layouts.app')

@section('subtitle', 'Nueva Novedad')
@section('content_header_title', 'Novedades')
@section('content_header_subtitle', 'Nueva Novedad')

@section('content_body')
    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header ">
                <h3 class="card-title">Registrar Novedad</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.guardias.show', $guardia) }}" class="btn btn-secondary btn-sm">
                        <img src="{{ asset('image/icons/volver.png') }}" alt="">
                    </a>
                </div>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.guardias.novedades.store', $guardia) }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Radio" {{ old('type') == 'Radio' ? 'selected' : '' }}>Radio</option>
                                    <option value="Fax" {{ old('type') == 'Fax' ? 'selected' : '' }}>Fax</option>
                                    <option
                                        value="Correo Electrónico"{{ old('type') == 'Correo Electrónico' ? 'selected' : '' }}>
                                        Correo Electrónico</option>
                                </select>
                                @error('type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Dirección</label>
                                <select name="direction" class="form-control @error('direction') is-invalid @enderror"
                                    required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Recibido" {{ old('direction') == 'Recibido' ? 'selected' : '' }}>Recibido
                                    </option>
                                    <option value="Expedido" {{ old('direction') == 'Expedido' ? 'selected' : '' }}>Expedido
                                    </option>
                                </select>
                                @error('direction')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group" id="grupo-destino" style="display:none;">
                                <label>Destino</label>
                                <input type="text" name="destino"
                                    class="form-control @error('destino') is-invalid @enderror"
                                    value="{{ old('destino') }}" placeholder="Ej: Cte.Rva.Gral.E.">
                                @error('destino')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group" id="grupo-organismo" style="display:none;">
                                <label>¿Quién expide?</label>
                                <select name="organismo_id" class="form-control">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach ($organismos as $organismo)
                                        <option value="{{ $organismo->id }}">{{ $organismo->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">O escribí uno nuevo abajo si no está en la lista:</small>
                                <input type="text" name="organismo_nuevo" class="form-control mt-1"
                                    placeholder="Nuevo organismo...">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Número</label>
                                <input type="text" name="number"
                                    class="form-control @error('number') is-invalid @enderror" value="{{ old('number') }}"
                                    required>
                                @error('number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hora</label>
                                <input type="time" name="time"
                                    class="form-control @error('time') is-invalid @enderror"
                                    value="{{ old('time', now()->format('H:i')) }}" required>
                                @error('time')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Oficina <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="office"
                                    class="form-control @error('office') is-invalid @enderror" value="{{ old('office') }}">
                                @error('office')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Clasificación</label>
                                <select name="clasification"
                                    class="form-control @error('clasification') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Rutinario" {{ old('clasification') == 'Rutinario' ? 'selected' : '' }}>
                                        Rutinario</option>
                                    <option value="Prioritario"
                                        {{ old('clasification') == 'Prioritario' ? 'selected' : '' }}>
                                        Prioritario</option>
                                    <option value="Urgente" {{ old('clasification') == 'Urgente' ? 'selected' : '' }}>
                                        Urgente</option>
                                    <option value="Destello" {{ old('clasification') == 'Destello' ? 'selected' : '' }}>
                                        Destello</option>
                                </select>
                                @error('clasification')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Asunto <small class="text-muted">(opcional)</small></label>
                            <input type="text" name="affair" class="form-control @error('affair') is-invalid @enderror"
                                value="{{ old('affair') }}">
                            @error('affair')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Texto</label>
                        <textarea name="text" rows="5" class="form-control @error('text') is-invalid @enderror" required>{{ old('text') }}</textarea>
                        @error('text')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.guardias.show', $guardia) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Registrar Novedad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@push('js')
    <script>
        $(document).ready(function() {
            function toggleOrganismo() {
                if ($('select[name="direction"]').val() === 'Recibido') {
                    $('#grupo-organismo').show();
                    $('#grupo-destino').hide();
                } else {
                    $('#grupo-organismo').hide();
                    $('#grupo-destino').show();
                }
            }
            $('select[name="direction"]').on('change', toggleOrganismo);
            toggleOrganismo();
        });
    </script>
@endpush
