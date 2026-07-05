@extends('layouts.app')

@section('subtitle', 'Editar Palomar')
@section('content_header_title', 'Palomares')
@section('content_header_subtitle', 'Editar: ' . $palomar->nombre)

@section('content_body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-dove"></i> Editar Palomar</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.palomares.update', $palomar) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label for="nombre">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $palomar->nombre) }}" required>
                    @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="ubicacion">Ubicación</label>
                    <input type="text" name="ubicacion" id="ubicacion" class="form-control @error('ubicacion') is-invalid @enderror" value="{{ old('ubicacion', $palomar->ubicacion) }}">
                    @error('ubicacion') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="capacidad_maxima">Capacidad máxima</label>
                    <input type="number" name="capacidad_maxima" id="capacidad_maxima" class="form-control @error('capacidad_maxima') is-invalid @enderror" value="{{ old('capacidad_maxima', $palomar->capacidad_maxima) }}" min="0">
                    @error('capacidad_maxima') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control @error('observaciones') is-invalid @enderror" rows="3">{{ old('observaciones', $palomar->observaciones) }}</textarea>
                    @error('observaciones') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="activo" id="activo" class="form-check-input" value="1" {{ old('activo', $palomar->activo) ? 'checked' : '' }}>
                    <label class="form-check-label" for="activo">Activo</label>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar</button>
                <a href="{{ route('admin.palomares.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@stop