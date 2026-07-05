@extends('layouts.app')

@section('subtitle', 'Registrar salida - Guardia ' . $guardia->date->format('d/m/Y'))
@section('content_header_title', 'Guardias')
@section('content_header_subtitle', 'Registrar salida de vehículo')

@section('content_body')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-truck"></i> Registrar salida de vehículo
                    <small class="text-muted ml-2">Guardia del {{ $guardia->date->format('d/m/Y') }}</small>
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.guardias.show', $guardia) }}" class="btn btn-outline-secondary btn-sm">
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

                <form action="{{ route('admin.guardias.salidas.store', $guardia) }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehiculo_id">Vehículo <span class="text-danger">*</span></label>
                                <select name="vehiculo_id" id="vehiculo_id"
                                    class="form-control @error('vehiculo_id') is-invalid @enderror" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach ($vehiculos as $vehiculo)
                                        <option value="{{ $vehiculo->id }}"
                                            {{ old('vehiculo_id') == $vehiculo->id ? 'selected' : '' }}>
                                            {{ $vehiculo->matricula }} - {{ $vehiculo->descripcion }}
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
                                <label for="conductor_id">Conductor <span class="text-danger">*</span></label>
                                <select name="conductor_id" id="conductor_id"
                                    class="form-control @error('conductor_id') is-invalid @enderror" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach ($conductores as $conductor)
                                        <option value="{{ $conductor->id }}"
                                            {{ old('conductor_id') == $conductor->id ? 'selected' : '' }}>
                                            {{ $conductor->nombre_visible }}
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
                                <label for="tipo_combustible">Tipo de combustible <span class="text-danger">*</span></label>
                                <select name="tipo_combustible" id="tipo_combustible"
                                    class="form-control @error('tipo_combustible') is-invalid @enderror" required>
                                    <option value="gas_oil" {{ old('tipo_combustible') == 'gas_oil' ? 'selected' : '' }}>
                                        Gas Oil</option>
                                    <option value="nafta" {{ old('tipo_combustible') == 'nafta' ? 'selected' : '' }}>Nafta
                                    </option>
                                </select>
                                @error('tipo_combustible')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="hora_sale">Hora de salida <span class="text-danger">*</span></label>
                                <input type="time" name="hora_sale" id="hora_sale"
                                    class="form-control @error('hora_sale') is-invalid @enderror"
                                    value="{{ old('hora_sale') }}" required>
                                @error('hora_sale')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="hora_entra">Hora de entrada</label>
                                <input type="time" name="hora_entra" id="hora_entra"
                                    class="form-control @error('hora_entra') is-invalid @enderror"
                                    value="{{ old('hora_entra') }}">
                                @error('hora_entra')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kms_sale">Kilómetros de salida</label>
                                <input type="number" name="kms_sale" id="kms_sale"
                                    class="form-control @error('kms_sale') is-invalid @enderror"
                                    value="{{ old('kms_sale') }}" min="0">
                                @error('kms_sale')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kms_entra">Kilómetros de entrada</label>
                                <input type="number" name="kms_entra" id="kms_entra"
                                    class="form-control @error('kms_entra') is-invalid @enderror"
                                    value="{{ old('kms_entra') }}" min="0">
                                @error('kms_entra')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="comision">Comisión / Motivo <span class="text-danger">*</span></label>
                                <textarea name="comision" id="comision" rows="3" class="form-control @error('comision') is-invalid @enderror"
                                    required>{{ old('comision') }}</textarea>
                                @error('comision')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.guardias.show', $guardia) }}" class="btn btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Registrar salida
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
