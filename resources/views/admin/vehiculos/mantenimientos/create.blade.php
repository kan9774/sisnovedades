@extends('layouts.app')

@section('subtitle', 'Nuevo Mantenimiento')
@section('content_header_title', 'Vehículos')
@section('content_header_subtitle', 'Nuevo Mantenimiento - ' . $vehiculo->matricula)

@section('content_body')
<div class="container-fluid">
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tools text-info"></i> Registrar Mantenimiento: <strong>{{ $vehiculo->matricula }}</strong>
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.vehiculos.show', $vehiculo) }}"
                   class="btn btn-outline-secondary btn-sm"
                   style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.vehiculos.mantenimientos.store', $vehiculo) }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="preventivo" {{ old('tipo') == 'preventivo' ? 'selected' : '' }}>Preventivo</option>
                                <option value="correctivo" {{ old('tipo') == 'correctivo' ? 'selected' : '' }}>Correctivo</option>
                                <option value="revision_tecnica" {{ old('tipo') == 'revision_tecnica' ? 'selected' : '' }}>Revisión Técnica</option>
                                <option value="otro" {{ old('tipo') == 'otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('tipo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fecha <span class="text-danger">*</span></label>
                            <input type="date"
                                   name="fecha"
                                   class="form-control @error('fecha') is-invalid @enderror"
                                   value="{{ old('fecha', now()->format('Y-m-d')) }}"
                                   required>
                            @error('fecha')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Kilometraje <small class="text-muted">(opcional)</small></label>
                            <input type="number"
                                   name="kilometraje"
                                   class="form-control @error('kilometraje') is-invalid @enderror"
                                   value="{{ old('kilometraje') }}"
                                   min="0">
                            @error('kilometraje')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Costo <small class="text-muted">(opcional)</small></label>
                            <input type="number"
                                   name="costo"
                                   class="form-control @error('costo') is-invalid @enderror"
                                   value="{{ old('costo') }}"
                                   step="0.01" min="0">
                            @error('costo')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción <span class="text-danger">*</span></label>
                    <textarea name="descripcion"
                              class="form-control @error('descripcion') is-invalid @enderror"
                              rows="3"
                              placeholder="Ej: Cambio de aceite y filtros, revisión de frenos"
                              required>{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Taller <small class="text-muted">(opcional)</small></label>
                            <input type="text"
                                   name="taller"
                                   class="form-control @error('taller') is-invalid @enderror"
                                   value="{{ old('taller') }}">
                            @error('taller')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Próximo mantenimiento (fecha) <small class="text-muted">(opcional)</small></label>
                            <input type="date"
                                   name="proximo_mantenimiento_fecha"
                                   class="form-control @error('proximo_mantenimiento_fecha') is-invalid @enderror"
                                   value="{{ old('proximo_mantenimiento_fecha') }}">
                            @error('proximo_mantenimiento_fecha')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Próximo mantenimiento (km) <small class="text-muted">(opcional)</small></label>
                            <input type="number"
                                   name="proximo_mantenimiento_km"
                                   class="form-control @error('proximo_mantenimiento_km') is-invalid @enderror"
                                   value="{{ old('proximo_mantenimiento_km') }}"
                                   min="0">
                            @error('proximo_mantenimiento_km')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.vehiculos.show', $vehiculo) }}" class="btn btn-outline-secondary btn-sm"
                       style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                        <i class="fas fa-save"></i> Guardar Mantenimiento
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
    });
</script>
@endpush