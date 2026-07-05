@extends('layouts.app')

@section('subtitle', 'Editar Organismo')
@section('content_header_title', 'Organismos')
@section('content_header_subtitle', 'Editar')

@section('content_body')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-building"></i> Editar Unidad
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

            <form action="{{ route('admin.organismos.update', $organismo) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" 
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $organismo->name) }}"
                           placeholder="Ej: J.Bn. Libertad o Muerte Com. Nº1"
                           required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" 
                            class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);"
                            aria-label="Actualizar unidad">
                        <i class="fas fa-save"></i> Actualizar Unidad
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