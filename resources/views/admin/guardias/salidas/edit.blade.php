@extends('layouts.app')

@section('subtitle', 'Editar Salida de Vehículo')
@section('content_header_title', 'Novedades')
@section('content_header_subtitle', 'Editar Salida de Vehículo')

@section('content_body')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-truck text-primary"></i> Editar Salida de Vehículo
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.guardias.novedades.show', $guardia) }}"
                   class="btn btn-outline-secondary btn-sm"
                   style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                   aria-label="Volver a la novedad">
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

            <form action="{{ route('admin.guardias.salidas.update', [$guardia, $salida]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Vehículo <span class="text-danger">*</span></label>
                            <select name="vehiculo_id"
                                    class="form-control select2 @error('vehiculo_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Seleccionar Vehículo --</option>
                                @foreach($vehiculos as $vehiculo)
                                    <option value="{{ $vehiculo->id }}"
                                        {{ old('vehiculo_id', $salida->vehiculo_id) == $vehiculo->id ? 'selected' : '' }}>
                                        {{ $vehiculo->matricula }} - {{ $vehiculo->descripcion ?? 'Sin descripción' }}
                                        @if($vehiculo->sin_cuentakilometros)
                                            (Sin cuentakm)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('vehiculo_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Conductor <span class="text-danger">*</span></label>
                            <select name="conductor_id"
                                    class="form-control select2 @error('conductor_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Seleccionar Conductor --</option>
                                @foreach($conductores as $conductor)
                                    <option value="{{ $conductor->id }}"
                                        {{ old('conductor_id', $salida->conductor_id) == $conductor->id ? 'selected' : '' }}>
                                        {{ $conductor->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('conductor_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipo de Combustible <span class="text-danger">*</span></label>
                            <select name="tipo_combustible"
                                    class="form-control @error('tipo_combustible') is-invalid @enderror"
                                    required>
                                <option value="">-- Seleccionar --</option>
                                <option value="gas_oil" {{ old('tipo_combustible', $salida->tipo_combustible) == 'gas_oil' ? 'selected' : '' }}>
                                    Gas Oil
                                </option>
                                <option value="nafta" {{ old('tipo_combustible', $salida->tipo_combustible) == 'nafta' ? 'selected' : '' }}>
                                    Nafta
                                </option>
                            </select>
                            @error('tipo_combustible')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Hora de Salida <span class="text-danger">*</span></label>
                            <input type="time"
                                   name="hora_sale"
                                   class="form-control @error('hora_sale') is-invalid @enderror"
                                   value="{{ old('hora_sale', $salida->hora_sale?->format('H:i')) }}"
                                   required>
                            @error('hora_sale')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Hora de Entrada <small class="text-muted">(opcional)</small></label>
                            <input type="time"
                                   name="hora_entra"
                                   class="form-control @error('hora_entra') is-invalid @enderror"
                                   value="{{ old('hora_entra', $salida->hora_entra?->format('H:i')) }}">
                            @error('hora_entra')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Km de Salida <small class="text-muted">(opcional)</small></label>
                            <input type="number"
                                   name="kms_sale"
                                   class="form-control @error('kms_sale') is-invalid @enderror"
                                   value="{{ old('kms_sale', $salida->kms_sale) }}"
                                   step="1"
                                   min="0">
                            @error('kms_sale')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Obligatorio si el vehículo tiene cuentakm</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Km de Entrada <small class="text-muted">(opcional)</small></label>
                            <input type="number"
                                   name="kms_entra"
                                   class="form-control @error('kms_entra') is-invalid @enderror"
                                   value="{{ old('kms_entra', $salida->kms_entra) }}"
                                   step="1"
                                   min="0">
                            @error('kms_entra')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Obligatorio si el vehículo tiene cuentakm</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="alert alert-info py-2 px-3">
                                <small>
                                    <i class="fas fa-info-circle"></i>
                                    @if($salida->kms_recorridos)
                                        Km recorridos: <strong>{{ $salida->kms_recorridos }}</strong>
                                        @if($salida->litros)
                                            | Litros: <strong>{{ number_format($salida->litros, 2) }}</strong>
                                        @endif
                                    @else
                                        Sin cálculos disponibles
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Comisión / Motivo <span class="text-danger">*</span></label>
                    <textarea name="comision"
                              class="form-control @error('comision') is-invalid @enderror"
                              rows="3"
                              required>{{ old('comision', $salida->comision) }}</textarea>
                    @error('comision')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.guardias.show', [$guardia, $novedad]) }}"
                       class="btn btn-outline-secondary btn-sm"
                       style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                        <i class="fas fa-save"></i> Actualizar Salida
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
        // Inicializar Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccionar...',
            allowClear: true
        });

        // Auto-ocultar alertas
        $('.alert').delay(4000).fadeOut('slow');
    });
</script>
@endpush