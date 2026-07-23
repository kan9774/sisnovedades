@extends('layouts.app')

@section('subtitle', 'Nuevo Vehículo')
@section('content_header_title', 'Vehículos')
@section('content_header_subtitle', 'Nuevo')

@section('content_body')
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck text-primary"></i> Nuevo Vehículo
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
                                    value="{{ old('matricula') }}" required>
                                @error('matricula')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Marca <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="marca"
                                    class="form-control @error('marca') is-invalid @enderror"
                                    value="{{ old('marca') }}">
                                @error('marca')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Modelo <small class="text-muted">(opcional)</small></label>
                                <input type="text" name="modelo"
                                    class="form-control @error('modelo') is-invalid @enderror"
                                    value="{{ old('modelo') }}">
                                @error('modelo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 2: Vehículo / Tipo de Vehículo / Unidad / Descripción --}}
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
                                    value="{{ old('descripcion') }}">
                                @error('descripcion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 3: Combustible / Lubricante / Rodado --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Combustible <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <select name="tipo_combustible_id" id="tipo_combustible_id"
                                        class="form-control @error('tipo_combustible_id') is-invalid @enderror" required>
                                        <option value="">-- Seleccionar --</option>
                                        @foreach ($tiposCombustible as $tipo)
                                            <option value="{{ $tipo->id }}"
                                                {{ old('tipo_combustible_id') == $tipo->id ? 'selected' : '' }}>
                                                {{ $tipo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        @livewire('catalogos.tipos-combustible-modal', key('combustible-modal-create'))
                                    </div>
                                </div>
                                @error('tipo_combustible_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Lubricante <small class="text-muted">(opcional)</small></label>
                                <div class="input-group input-group-sm">
                                    <select name="tipo_lubricante_id" id="tipo_lubricante_id"
                                        class="form-control @error('tipo_lubricante_id') is-invalid @enderror">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach ($tiposLubricante as $tipo)
                                            <option value="{{ $tipo->id }}"
                                                {{ old('tipo_lubricante_id') == $tipo->id ? 'selected' : '' }}>
                                                {{ $tipo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        @livewire('catalogos.tipos-lubricante-modal', key('lubricante-modal-create'))
                                    </div>
                                </div>
                                @error('tipo_lubricante_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo de Rodado <small class="text-muted">(opcional)</small></label>
                                <div class="input-group input-group-sm">
                                    <select name="tipo_rodado_id" id="tipo_rodado_id"
                                        class="form-control @error('tipo_rodado_id') is-invalid @enderror">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach ($tiposRodado as $tipo)
                                            <option value="{{ $tipo->id }}"
                                                {{ old('tipo_rodado_id') == $tipo->id ? 'selected' : '' }}>
                                                {{ $tipo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        @livewire('catalogos.tipos-rodado-modal', key('rodado-modal-create'))
                                    </div>
                                </div>
                                @error('tipo_rodado_id')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Fila 4: Consumo / Odómetro --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Consumo (L/km) <small class="text-muted">(opcional)</small></label>
                                <input type="number" name="consumo_litros_por_km"
                                    class="form-control @error('consumo_litros_por_km') is-invalid @enderror"
                                    value="{{ old('consumo_litros_por_km') }}" step="0.0001">
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

                    {{-- Fila 5: Ejes / Chasis / Motor / Activo / Estado --}}
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Ejes</label>
                                <input type="number" name="ejes"
                                    class="form-control @error('ejes') is-invalid @enderror"
                                    value="{{ old('ejes') }}" min="1" max="10">
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
                                        value="1" {{ old('activo', true) ? 'checked' : '' }}>
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
                                         Verde</option>
                                    <option value="amarillo" {{ old('estado') == 'amarillo' ? 'selected' : '' }}>
                                         Amarillo</option>
                                    <option value="rojo" {{ old('estado') == 'rojo' ? 'selected' : '' }}>
                                         Rojo</option>
                                    <option value="negro" {{ old('estado') == 'negro' ? 'selected' : '' }}>
                                         Negro</option>
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
                            <i class="fas fa-save"></i> Guardar Vehículo
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

        function actualizarSelect(selectId, id, nombre) {
            const select = document.getElementById(selectId);
            if (!select) return;
            let option = select.querySelector(`option[value="${id}"]`);
            if (!option) {
                option = new Option(nombre, id);
                select.appendChild(option);
            } else {
                option.text = nombre;
            }
            select.value = id;
        }

        window.addEventListener('combustible-actualizado', e => {
            actualizarSelect('tipo_combustible_id', e.detail.id, e.detail.nombre);
        });
        window.addEventListener('lubricante-actualizado', e => {
            actualizarSelect('tipo_lubricante_id', e.detail.id, e.detail.nombre);
        });
        window.addEventListener('rodado-actualizado', e => {
           actualizarSelect('tipo_rodado_id', e.detail.id, e.detail.nombre);
        });
    </script>
@endpush