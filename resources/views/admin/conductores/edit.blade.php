@extends('layouts.app')

@section('subtitle', 'Editar Conductor')
@section('content_header_title', 'Conductores')
@section('content_header_subtitle', 'Editar')

@section('content_body')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-tie text-primary"></i> Editar Conductor: <strong>{{ $conductor->nombre_completo }}</strong>
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.conductores.index') }}"
                   class="btn btn-outline-secondary btn-sm"
                   style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                   aria-label="Volver al listado">
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

            <form action="{{ route('admin.conductores.update', $conductor) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Datos personales --}}
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Grado <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="grado"
                                   class="form-control @error('grado') is-invalid @enderror"
                                   value="{{ old('grado', $conductor->grado) }}"
                                   required>
                            @error('grado')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Primer Nombre <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="primer_nombre"
                                   class="form-control @error('primer_nombre') is-invalid @enderror"
                                   value="{{ old('primer_nombre', $conductor->primer_nombre) }}"
                                   required>
                            @error('primer_nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Segundo Nombre <small class="text-muted">(opcional)</small></label>
                            <input type="text"
                                   name="segundo_nombre"
                                   class="form-control @error('segundo_nombre') is-invalid @enderror"
                                   value="{{ old('segundo_nombre', $conductor->segundo_nombre) }}">
                            @error('segundo_nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Documento <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="documento"
                                   class="form-control @error('documento') is-invalid @enderror"
                                   value="{{ old('documento', $conductor->documento) }}"
                                   required>
                            @error('documento')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Primer Apellido <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="primer_apellido"
                                   class="form-control @error('primer_apellido') is-invalid @enderror"
                                   value="{{ old('primer_apellido', $conductor->primer_apellido) }}"
                                   required>
                            @error('primer_apellido')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Segundo Apellido <small class="text-muted">(opcional)</small></label>
                            <input type="text"
                                   name="segundo_apellido"
                                   class="form-control @error('segundo_apellido') is-invalid @enderror"
                                   value="{{ old('segundo_apellido', $conductor->segundo_apellido) }}">
                            @error('segundo_apellido')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Licencia de conducir --}}
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>N° Licencia <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="nro_licencia"
                                   class="form-control @error('nro_licencia') is-invalid @enderror"
                                   value="{{ old('nro_licencia', $conductor->nro_licencia) }}"
                                   required>
                            @error('nro_licencia')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Categoría <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="categoria_licencia"
                                   class="form-control @error('categoria_licencia') is-invalid @enderror"
                                   value="{{ old('categoria_licencia', $conductor->categoria_licencia) }}"
                                   required>
                            @error('categoria_licencia')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Vencimiento Licencia <span class="text-danger">*</span></label>
                            <input type="date"
                                   name="fecha_vencimiento_licencia"
                                   class="form-control @error('fecha_vencimiento_licencia') is-invalid @enderror"
                                   value="{{ old('fecha_vencimiento_licencia', $conductor->fecha_vencimiento_licencia?->format('Y-m-d')) }}"
                                   required>
                            @error('fecha_vencimiento_licencia')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Carné de Salud --}}
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Lugar Carné Salud <small class="text-muted">(opcional)</small></label>
                            <input type="text"
                                   name="lugar_carne_salud"
                                   class="form-control @error('lugar_carne_salud') is-invalid @enderror"
                                   value="{{ old('lugar_carne_salud', $conductor->lugar_carne_salud) }}">
                            @error('lugar_carne_salud')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Vencimiento Carné Salud <small class="text-muted">(opcional)</small></label>
                            <input type="date"
                                   name="fecha_vencimiento_carne_salud"
                                   class="form-control @error('fecha_vencimiento_carne_salud') is-invalid @enderror"
                                   value="{{ old('fecha_vencimiento_carne_salud', $conductor->fecha_vencimiento_carne_salud?->format('Y-m-d')) }}">
                            @error('fecha_vencimiento_carne_salud')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="activo"
                                       name="activo"
                                       {{ old('activo', $conductor->activo) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Carné Habilitante --}}
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Lugar Carné Habilitante <small class="text-muted">(opcional)</small></label>
                            <input type="text"
                                   name="lugar_carne_habilitante"
                                   class="form-control @error('lugar_carne_habilitante') is-invalid @enderror"
                                   value="{{ old('lugar_carne_habilitante', $conductor->lugar_carne_habilitante) }}">
                            @error('lugar_carne_habilitante')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Vencimiento Carné Habilitante <small class="text-muted">(opcional)</small></label>
                            <input type="date"
                                   name="fecha_vencimiento_carne_habilitante"
                                   class="form-control @error('fecha_vencimiento_carne_habilitante') is-invalid @enderror"
                                   value="{{ old('fecha_vencimiento_carne_habilitante', $conductor->fecha_vencimiento_carne_habilitante?->format('Y-m-d')) }}">
                            @error('fecha_vencimiento_carne_habilitante')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipo Vehículo Habilitado <small class="text-muted">(opcional)</small></label>
                            <input type="text"
                                   name="tipo_vehiculo_habilitado"
                                   class="form-control @error('tipo_vehiculo_habilitado') is-invalid @enderror"
                                   value="{{ old('tipo_vehiculo_habilitado', $conductor->tipo_vehiculo_habilitado) }}"
                                    placeholder="Ej: 7Ton, 18 Pasajeros, S/Limite de Peso">
                            @error('tipo_vehiculo_habilitado')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="form-group mt-3">
                    <label>Observaciones <small class="text-muted">(opcional)</small></label>
                    <textarea name="observaciones"
                              class="form-control @error('observaciones') is-invalid @enderror"
                              rows="3">{{ old('observaciones', $conductor->observaciones) }}</textarea>
                    @error('observaciones')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.conductores.index') }}" class="btn btn-outline-secondary btn-sm"
                       style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                        <i class="fas fa-save"></i> Actualizar Conductor
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