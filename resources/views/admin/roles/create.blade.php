@extends('layouts.app')

@section('subtitle', 'Nuevo Rol')
@section('content_header_title', 'Roles')
@section('content_header_subtitle', 'Nuevo')

@section('content_body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Crear rol</h3>
        </div>
        <div class="card-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Nombre <small class="text-muted">(sin espacios, ej: oficial_de_dia)</small></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required>
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Descripción <small class="text-muted">(opcional)</small></label>
                    <input type="text" name="description"
                           class="form-control @error('description') is-invalid @enderror"
                           value="{{ old('description') }}">
                    @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Permisos</label>
                    <div class="row">
                        @foreach($permisos as $permiso)
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox"
                                           class="custom-control-input"
                                           id="permiso_{{ $permiso->id }}"
                                           name="permisos[]"
                                           value="{{ $permiso->id }}"
                                           {{ in_array($permiso->id, old('permisos', [])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="permiso_{{ $permiso->id }}">
                                        {{ ucfirst(str_replace('_', ' ', $permiso->name)) }}
                                        <br>
                                        <small class="text-muted">{{ $permiso->description }}</small>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop