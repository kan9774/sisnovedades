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
                    <label>Archivo (PDF, Word o Txt máx. 10MB)</label>
                    <div class="custom-file">
                        <input type="file" name="archivo" class="custom-file-input" id="archivo" accept=".pdf,.docx,.doc">
                        <label class="custom-file-label" for="archivo">Elegir archivo...</label>
                    </div>
                </div>
                
                {{-- Preview del archivo --}}
                @if (old('archivo'))
                    <div class="preview-container mt-2">
                        <span class="preview-label">Preview:</span>
                        @if (old('archivo')->isImage())
                            <img src="{{ old('archivo')->temporaryUrl() }}" alt="Preview" class="preview-image" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                        @else
                            <div class="preview-placeholder">
                                <i class="fas fa-file"></i>
                                <span>{{ old('archivo')->getClientOriginalName() }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload mr-1"></i> Subir documento
                </button>
                <a href="{{ route('admin.documentos.index') }}" class="btn btn-secondary">Cancelar</a>
                
                {{-- Barra de progreso --}}
                <div class="progress-bar mt-2" style="display: none;">
                    <div class="progress-bar-fill" id="progress-bar" style="width: 0%;"></div>
                </div>
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
        
        // Barra de progreso
        $(document).on('submit', function(e) {
            if ($('#archivo').val()) {
                $('#progress-bar').fadeIn();
                var progress = 0;
                var interval = setInterval(function() {
                    progress += 10;
                    $('#progress-bar').css('width', progress + '%');
                    if (progress >= 100) {
                        clearInterval(interval);
                        $('#progress-bar').css('width', '100%');
                    }
                }, 50);
            }
        });
    </script>
@stop