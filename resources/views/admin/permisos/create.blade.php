@extends('layouts.app')

@section('subtitle', 'Nuevo Permiso')
@section('content_header_title', 'Permisos')
@section('content_header_subtitle', 'Nuevo')

@section('content_body')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-key text-primary"></i> Crear permiso
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.permisos.index') }}" 
                   class="btn btn-outline-secondary btn-sm"
                   style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                   aria-label="Volver al listado de permisos">
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

            <form action="{{ route('admin.permisos.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Nombre <small class="text-muted">(sin espacios, ej: ver_reportes)</small></label>
                    <input type="text" name="name" id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="Ej: ver_reportes"
                           required>
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="description">Descripción <small class="text-muted">(opcional)</small></label>
                    <input type="text" name="description" id="description"
                           class="form-control @error('description') is-invalid @enderror"
                           value="{{ old('description') }}"
                           placeholder="Ej: Puede ver los reportes del sistema">
                    @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-outline-primary btn-sm"
                            style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);"
                            aria-label="Guardar nuevo permiso">
                        <i class="fas fa-save"></i> Crear Permiso
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