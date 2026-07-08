@extends('layouts.app')

@section('subtitle', 'Nuevo Vehículo')
@section('content_header_title', 'Vehículos')
@section('content_header_subtitle', 'Nuevo')

@section('content_body')
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck text-primary"></i> Crear Vehículo
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.vehiculos.index') }}" class="btn btn-outline-secondary btn-sm"
                        style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                        aria-label="Volver al listado">
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

                <form action="{{ route('admin.vehiculos.store') }}" method="POST">
                    @csrf

                    {{-- Fila 1: Matrícula / Marca / Modelo --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Matrícula <span class="text-danger">*</span></label>
                                <input type="text" name="matricula"
                                    class="form-control @error('matricula') is-invalid @enderror"
                                    value="{{ old('matricula') }}" placeholder="Ej: ABC-123" required>
                                @error('matricula')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Marca <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="marca"
                                    class="form-control @error('marca') is-invalid @enderror" value="{{ old('marca') }}"
                                    placeholder="Ej: Toyota">
                                @error('marca')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Modelo <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="modelo"
                                    class="form-control @error('modelo') is-invalid @enderror" value="{{ old('modelo') }}"
                                    placeholder="Ej: Hilux 4x4">
                                @error('modelo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 2: Color / Tipo de Vehículo / Unidad / Descripción --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Vehículo <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="vehiculo"
                                    class="form-control @error('vehiculo') is-invalid @enderror"
                                    value="{{ old('vehiculo') }}" placeholder="Ej: JEEP, PICKUP, CAMIÓN">
                                @error('vehiculo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo de Vehículo <small class="text-muted">(opcional)</small></label>
                                <select name="tipo_vehiculo_id"
                                    class="form-control @error('tipo_vehiculo_id') is-invalid @enderror">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach ($tiposVehiculo as $tipo)
                                        <option value="{{ $tipo->id }}"
                                            {{ old('tipo_vehiculo_id') == $tipo->id ? 'selected' : '' }}>
                                            {{ $tipo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tipo_vehiculo_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Unidad <small class="text-muted">(opcional)</small></label>
                                <select name="unidad_id" class="form-control @error('unidad_id') is-invalid @enderror">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach ($unidades as $unidad)
                                        <option value="{{ $unidad->id }}"
                                            {{ old('unidad_id') == $unidad->id ? 'selected' : '' }}>
                                            {{ $unidad->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unidad_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Objeto <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="descripcion"
                                    class="form-control @error('descripcion') is-invalid @enderror"
                                    value="{{ old('descripcion') }}" placeholder="Ej: Transporte de Personal">
                                @error('descripcion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 3: Combustible / Consumo / Sin cuentakilómetros --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Combustible <span class="text-danger">*</span></label>
                                <select name="tipo_combustible"
                                    class="form-control @error('tipo_combustible') is-invalid @enderror" required>
                                    <option value="">-- Seleccionar --</option>
                                    <option value="gas_oil" {{ old('tipo_combustible') == 'gas_oil' ? 'selected' : '' }}>
                                        Gas Oil
                                    </option>
                                    <option value="nafta" {{ old('tipo_combustible') == 'nafta' ? 'selected' : '' }}>
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
                                <label>Consumo (L/km) <small class="text-muted">(opcional)</small></label>
                                <input type="number" name="consumo_litros_por_km"
                                    class="form-control @error('consumo_litros_por_km') is-invalid @enderror"
                                    value="{{ old('consumo_litros_por_km') }}" step="0.0001" placeholder="Ej: 0.12">
                                @error('consumo_litros_por_km')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="sin_cuentakilometros"
                                        name="sin_cuentakilometros" {{ old('sin_cuentakilometros') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="sin_cuentakilometros">
                                        Sin cuentakilómetros
                                    </label>
                                </div>
                                <small class="text-muted d-block">Marcar si el vehículo no tiene cuentakm</small>
                            </div>
                        </div>
                    </div>

                    {{-- Fila 4: Ejes / Chasis / Motor / Activo / Estado --}}
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Ejes</label>
                                <input type="number" name="ejes"
                                    class="form-control @error('ejes') is-invalid @enderror" value="{{ old('ejes', 2) }}"
                                    min="1" max="10">
                                @error('ejes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>N° Chasis <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="numero_chasis"
                                    class="form-control @error('numero_chasis') is-invalid @enderror"
                                    value="{{ old('numero_chasis') }}">
                                @error('numero_chasis')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>N° Motor <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="numero_motor"
                                    class="form-control @error('numero_motor') is-invalid @enderror"
                                    value="{{ old('numero_motor') }}">
                                @error('numero_motor')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="activo" name="activo"
                                        {{ old('activo', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="activo">Activo</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <select name="estado" id="estado"
                                    class="form-control @error('estado') is-invalid @enderror">
                                    <option value="verde" {{ old('estado', 'verde') == 'verde' ? 'selected' : '' }}>
                                        🟢 Verde</option>
                                    <option value="amarillo" {{ old('estado') == 'amarillo' ? 'selected' : '' }}>
                                        🟡 Amarillo</option>
                                    <option value="rojo" {{ old('estado') == 'rojo' ? 'selected' : '' }}>
                                        🔴 Rojo</option>
                                    <option value="negro" {{ old('estado') == 'negro' ? 'selected' : '' }}>
                                        ⚫ Negro</option>
                                </select>
                                @error('estado')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.vehiculos.index') }}" class="btn btn-outline-secondary btn-sm"
                            style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                            <i class="fas fa-save"></i> Crear Vehículo
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
