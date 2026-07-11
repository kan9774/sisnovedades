@extends('layouts.app')

@section('subtitle', 'Documentos')
@section('content_header_title', 'Documentos')
@section('content_header_subtitle', 'Manuales y reglamentos')

@section('content_body')
    <div class="container-fluid">
        {{-- ALERTAS DE ÉXITO O ERROR (Mantener las tuyas) --}}
        @if (session('success'))
            ...
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-layer-group"></i> Centro de Documentación y Manuales</h3>
                <div class="card-tools">
                    {{-- Tus botones actuales de Papelera, Categorías y Crear --}}
                    <a href="{{ route('admin.documentos.trashed') }}" class="btn btn-outline-secondary btn-sm"><i
                            class="fas fa-trash"></i> Papelera</a>
                    <a href="{{ route('admin.documentos.categorias.index') }}" class="btn btn-outline-secondary btn-sm"><i
                            class="fas fa-folder"></i> Categorías</a>
                    @can('create', App\Models\Documento::class)
                        <a href="{{ route('admin.documentos.create') }}" class="btn btn-primary btn-sm"><i
                                class="fas fa-plus-circle"></i> Subir documento</a>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                {{-- SECCIÓN 1: BUSCADOR EN TIEMPO REAL --}}
                <div class="form-group mb-4">
                    <div class="input-group input-group-lg">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i
                                    class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="searchDocumento" class="form-control border-left-0"
                            placeholder="¿Qué manual o reglamento estás buscando? Digita el título...">
                    </div>
                </div>

                {{-- SECCIÓN 2: FILTRO ESTILO CARPETAS (Categorías) --}}
                <h5 class="mb-3 text-muted font-weight-bold"><i class="fas fa-folder-open text-warning"></i> Explorar por
                    Categorías</h5>
                <div class="row mb-4">
                    <!-- Botón para volver a mostrar Todos -->
                    <div class="col-md-3 col-sm-6 mb-2">
                        <a href="{{ route('admin.documentos.index') }}"
                            class="btn btn-block p-3 text-left d-flex align-items-center {{ !request('categoria_id') ? 'btn-secondary text-white shadow-sm' : 'btn-outline-secondary' }}">
                            <i
                                class="fas fa-boxes fa-2x mr-3 {{ !request('categoria_id') ? 'text-white' : 'text-secondary' }}"></i>
                            <div class="text-truncate">
                                <span class="d-block font-weight-bold mb-0">Todos los archivos</span>
                                <small class="{{ !request('categoria_id') ? 'text-white-50' : 'text-muted' }}">Mostrar
                                    todo</small>
                            </div>
                        </a>
                    </div>
                    <!-- Listado de tus categorías -->
                    @foreach ($categorias as $categoria)
                        <div class="col-md-3 col-sm-6 mb-2">
                            <a href="{{ route('admin.documentos.index', ['categoria_id' => $categoria->id]) }}"
                                class="btn btn-block p-3 text-left d-flex align-items-center {{ request('categoria_id') == $categoria->id ? 'btn-secondary text-white shadow-sm' : 'btn-outline-secondary' }}">
                                <i
                                    class="fas fa-folder fa-2x mr-3 {{ request('categoria_id') == $categoria->id ? 'text-white' : 'text-warning' }}"></i>
                                <div class="text-truncate">
                                    <span class="d-block font-weight-bold mb-0">{{ $categoria->nombre }}</span>
                                    <small
                                        class="{{ request('categoria_id') == $categoria->id ? 'text-white-50' : 'text-muted' }}">Ver
                                        documentos</small>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <hr class="my-4">

                {{-- SECCIÓN 3: CUADRÍCULA DE DOCUMENTOS (TARJETAS) --}}
                <h5 class="mb-3 text-muted font-weight-bold">
                    <i class="fas fa-file-alt"></i>
                    {{ request('categoria_id') ? 'Archivos en esta categoría' : 'Documentos recientes' }}
                </h5>

                <div class="row" id="documentosContainer">
                    @forelse($documentos as $documento)
                        <div class="col-md-4 col-sm-6 mb-4 documento-card">
                            <div class="card h-100 shadow-sm border">
                                <div class="card-body d-flex flex-column p-3">

                                    <div class="d-flex align-items-start mb-2">
                                        <!-- Icono dinámico grande -->
                                        <div class="mr-3">
                                            @if ($documento->extension === 'pdf')
                                                <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                            @elseif(in_array($documento->extension, ['doc', 'docx']))
                                                <i class="fas fa-file-word fa-3x text-primary"></i>
                                            @else
                                                <i class="fas fa-file-alt fa-3x text-secondary"></i>
                                            @endif
                                        </div>
                                        <div class="text-truncate w-100">
                                            <!-- Título para la búsqueda -->
                                            <h6 class="font-weight-bold mb-1 text-dark text-truncate doc-titulo"
                                                title="{{ $documento->titulo }}">
                                                {{ $documento->titulo }}
                                            </h6>
                                            <span
                                                class="badge badge-light border text-muted px-2 py-1 text-xs">{{ $documento->categoria->nombre }}</span>
                                        </div>
                                    </div>

                                    <!-- Descripción corta -->
                                    <p class="text-muted text-xs flex-grow-1 mb-3">
                                        {{ Str::limit($documento->descripcion ?? 'Sin descripción adicional.', 85) }}
                                    </p>

                                    <div class="mt-auto">
                                        <div
                                            class="d-flex justify-content-between align-items-center text-xs text-muted mb-2 border-top pt-2">
                                            <span><i class="fas fa-database"></i> {{ $documento->tamanio_legible }}</span>
                                            <span><i class="fas fa-calendar-alt"></i>
                                                {{ $documento->created_at->format('d/m/Y') }}</span>
                                        </div>

                                        <!-- Botones de Acción rápidos -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted text-truncate" style="max-width: 110px;">
                                                <i class="fas fa-user"></i> {{ $documento->subidoPor->name ?? 'Sistema' }}
                                            </small>
                                            <div>
                                                @if ($documento->extension === 'pdf')
                                                    <a href="{{ route('admin.documentos.preview', $documento) }}"
                                                        class="btn btn-outline-info btn-sm px-2" title="Previsualizar"
                                                        target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('admin.documentos.download', $documento) }}"
                                                    class="btn btn-primary btn-sm px-2 ml-1" title="Descargar">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                @can('delete', $documento)
                                                    <form action="{{ route('admin.documentos.destroy', $documento) }}"
                                                        method="POST" class="d-inline ml-1"
                                                        onsubmit="return confirm('¿Eliminar este documento?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-outline-danger btn-sm px-2"
                                                            title="Mover a papelera"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 text-gray"></i>
                            <p>No se encontraron documentos en esta sección.</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        // Buscador interactivo por título en tiempo real
        document.getElementById('searchDocumento').addEventListener('keyup', function() {
            let value = this.value.toLowerCase();
            let cards = document.querySelectorAll('.documento-card');

            cards.forEach(function(card) {
                let titulo = card.querySelector('.doc-titulo').textContent.toLowerCase();
                if (titulo.includes(value)) {
                    card.style.setProperty('display', '', 'important');
                } else {
                    card.style.setProperty('display', 'none', 'important');
                }
            });
        });
    </script>
@stop
