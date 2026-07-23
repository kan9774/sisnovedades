@extends('layouts.app')

@section('subtitle', 'Nueva Oficina')
@section('content_header_title', 'Oficinas')
@section('content_header_subtitle', 'Nueva')

@section('content_body')
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building"></i> Crear Oficina</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.oficinas.index') }}" class="btn btn-outline-secondary btn-sm"
                        style="background-color: rgba(108, 117, 125, 0.08);">
                        <i class="fas fa-arrow-left"></i> Volver
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

                <form action="{{ route('admin.oficinas.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                            value="{{ old('nombre') }}" placeholder="Ej: Oficina S-1" required>
                        @error('nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input type="hidden" name="activo" value="0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1"
                                {{ old('activo', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="activo">Activa</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.oficinas.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-save"></i> Crear Oficina
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
