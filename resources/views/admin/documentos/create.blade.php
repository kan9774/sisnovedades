@extends('layouts.app')

@section('subtitle', 'Subir documento')
@section('content_header_title', 'Subir documento')
@section('content_header_subtitle', 'Nuevo manual o reglamento')

@section('content_body')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-upload"></i> Nuevo documento</h3>
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

            <form action="{{ route('admin.documentos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_documento_id" class="form-control">
                        <option value="">Seleccionar...</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('categoria_documento_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Título</label>
                    <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}">
                </div>

                <div class="form-group">
                    <label>Descripción (opcional)</label>
                    <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Archivo (PDF, Word o Txt máx. 100MB)</label>
                    <div class="custom-file">
                        <input type="file" name="archivo" class="custom-file-input" id="archivo" accept=".pdf,.docx,.doc">
                        <label class="custom-file-label" for="archivo">Elegir archivo...</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload mr-1"></i> Subir documento
                </button>
                <a href="{{ route('admin.documentos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
    <script>
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
@stop