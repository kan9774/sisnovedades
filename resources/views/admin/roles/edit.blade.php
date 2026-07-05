@extends('layouts.app')

@section('subtitle', 'Editar Rol')
@section('content_header_title', 'Roles')
@section('content_header_subtitle', 'Editar')

@section('content_body')
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Editar rol: {{ ucfirst(str_replace('_', ' ', $rol->name)) }}</h3>
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

            <form action="{{ route('admin.roles.update', $rol) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $rol->name) }}" required>
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>Descripción <small class="text-muted">(opcional)</small></label>
                    <input type="text" name="description"
                           class="form-control @error('description') is-invalid @enderror"
                           value="{{ old('description', $rol->description) }}">
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
                                           {{ in_array($permiso->id, old('permisos', $rol->permisos->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop