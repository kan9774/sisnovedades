@extends('layouts.app')

@section('subtitle', 'Nueva Novedad')
@section('content_header_title', 'Novedades')
@section('content_header_subtitle', 'Nueva')

@section('content_body')
    <div class="container-fluid">

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-tower-cell text-primary"></i> Registrar Novedad
                    <span class="text-muted font-weight-normal ml-1">— Guardia {{ $guardia->date->format('d/m/Y') }}</span>
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.guardias.show', $guardia) }}" class="btn btn-outline-secondary btn-sm"
                        style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                        aria-label="Volver a la guardia">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.guardias.novedades.store', $guardia) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- Fila 1: Tipo / Dirección / Destino / Organismo --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select name="type" id="type"
                                    class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Radio" {{ old('type') == 'Radio' ? 'selected' : '' }}>Radio</option>
                                    <option value="Fax" {{ old('type') == 'Fax' ? 'selected' : '' }}>Fax</option>
                                    <option value="Correo Electrónico"
                                        {{ old('type') == 'Correo Electrónico' ? 'selected' : '' }}>
                                        Correo Electrónico</option>
                                </select>
                                @error('type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Dirección <span class="text-danger">*</span></label>
                                <select name="direction" id="direction"
                                    class="form-control @error('direction') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Recibido" {{ old('direction') == 'Recibido' ? 'selected' : '' }}>
                                        Recibido</option>
                                    <option value="Expedido" {{ old('direction') == 'Expedido' ? 'selected' : '' }}>
                                        Expedido</option>
                                </select>
                                @error('direction')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <div class="form-group" id="grupo-organismo" style="display:none;">
                                <label>¿Quién expide?</label>
                                <select name="organismo_id" class="form-control">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach ($organismos as $organismo)
                                        <option value="{{ $organismo->id }}">{{ $organismo->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">O escribí uno nuevo:</small>
                                <input type="text" name="organismo_nuevo" class="form-control mt-1"
                                    placeholder="Nuevo organismo...">
                            </div>
                        </div>
                    </div>

                    {{-- Fila 2: Número / Hora / Oficina --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Número <span class="text-danger">*</span></label>
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
                                <label>Hora <span class="text-danger">*</span></label>
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
                                <label>Oficina <span class="text-danger">*</span></label>
                                <select name="office_id" class="form-control @error('office_id') is-invalid @enderror"
                                    required>
                                    <option value="">-- Seleccionar --</option>
                                    @foreach ($oficinas as $oficina)
                                        <option value="{{ $oficina->id }}"
                                            {{ old('office_id') == $oficina->id ? 'selected' : '' }}>
                                            {{ $oficina->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('office_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 3: Clasificación / Asunto --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Clasificación <span class="text-danger">*</span></label>
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
                            <div class="form-group">
                                <label>Asunto <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="affair"
                                    class="form-control @error('affair') is-invalid @enderror"
                                    value="{{ old('affair') }}">
                                @error('affair')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 4: Texto --}}
                    <div class="form-group">
                        <label>Texto <span class="text-danger">*</span></label>
                        <textarea name="text" rows="5" class="form-control @error('text') is-invalid @enderror" required>{{ old('text') }}</textarea>
                        @error('text')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Fila 5: Adjunto --}}
                    <div class="form-group">
                        <label>
                            Adjunto <small class="text-muted">(opcional — PDF, JPG, PNG, máx. 10MB)</small>
                        </label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('archivo') is-invalid @enderror"
                                name="archivo" id="archivo" accept=".pdf,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="archivo">Seleccionar archivo</label>
                        </div>
                        @error('archivo')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.guardias.show', $guardia) }}" class="btn btn-outline-secondary btn-sm"
                            style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
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
            $('.alert').delay(4000).fadeOut('slow');

            function toggleOrganismo() {
                if ($('#direction').val() === 'Recibido') {
                    $('#grupo-organismo').show();
                    $('#grupo-destino').hide();
                } else {
                    $('#grupo-organismo').hide();
                    $('#grupo-destino').show();
                }
            }

            $('#direction').on('change', toggleOrganismo);

            toggleOrganismo();

            $('#archivo').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').text(fileName || 'Seleccionar archivo');
            });
        });
    </script>
@endpush
