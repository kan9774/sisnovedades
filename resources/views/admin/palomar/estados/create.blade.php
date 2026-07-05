@extends('layouts.app')

@section('subtitle', 'Nuevo Estado')
@section('content_header_title', 'Estados')
@section('content_header_subtitle', 'Crear')

@section('content_body')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tag"></i> Nuevo Estado</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.estados-paloma.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre"
                            class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="color" name="color" id="color"
                            class="form-control @error('color') is-invalid @enderror"
                            value="{{ old('color', $estado->color ?? '#6c757d') }}"
                            style="height: 40px; padding: 2px; cursor: pointer;">
                        @error('color')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="activo" id="activo" class="form-check-input" value="1"
                            {{ old('activo', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    <a href="{{ route('admin.estados-paloma.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
@stop
