@extends('layouts.app')

@section('subtitle', 'Nueva Unidad')
@section('content_header_title', 'Unidades')
@section('content_header_subtitle', 'Nueva')

@section('content_body')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-building"></i> Crear Unidad
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.organismos.index') }}" 
                   class="btn btn-outline-secondary btn-sm"
                   style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                   aria-label="Volver al listado de organismos">
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

            <form action="{{ route('admin.organismos.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" 
                           name="name"
                           id="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="Ej: J.Bn. Libertad o Muerte Com. Nº1"
                           required>
                    @error('nombre')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" 
                            class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);"
                            aria-label="Guardar nueva unidad">
                        <i class="fas fa-save"></i> Crear Unidad
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
        // Auto-ocultar alertas después de 4 segundos
        $('.alert').delay(4000).fadeOut('slow');
    });
</script>
@endpush