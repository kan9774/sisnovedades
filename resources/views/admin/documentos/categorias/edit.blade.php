@extends('layouts.app')

@section('subtitle', 'Editar categoría')
@section('content_header_title', 'Editar categoría')
@section('content_header_subtitle', 'Documentos')

@section('content_body')
<div class="container-fluid">
    <div class="card">
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

            <form action="{{ route('admin.documentos.categorias.update', $categoria) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $categoria->nombre) }}">
                </div>

                <div class="form-group">
                    <label>Descripción (opcional)</label>
                    <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Actualizar
                </button>
                <a href="{{ route('admin.documentos.categorias.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@stop