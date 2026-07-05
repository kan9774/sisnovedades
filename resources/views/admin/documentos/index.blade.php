@extends('layouts.app')

@section('subtitle', 'Documentos')
@section('content_header_title', 'Documentos')
@section('content_header_subtitle', 'Manuales y reglamentos')

@section('content_body')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt"></i> Documentos cargados</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.documentos.trashed') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-trash"></i> Papelera
                    </a>
                    <a href="{{ route('admin.documentos.categorias.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-folder"></i> Categorías
                    </a>
                    @can('create', App\Models\Documento::class)
                        <a href="{{ route('admin.documentos.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus-circle"></i> Subir documento
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="categoria_id" class="form-control">
                                <option value="">Todas las categorías</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}"
                                        {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i>
                                Filtrar</button>
                            <a href="{{ route('admin.documentos.index') }}" class="btn btn-secondary btn-sm">Limpiar</a>
                        </div>
                    </div>
                </form>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Tipo</th>
                            <th>Tamaño</th>
                            <th>Subido por</th>
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documentos as $documento)
                            <tr>
                                <td>{{ $documento->titulo }}</td>
                                <td><span class="badge bg-secondary">{{ $documento->categoria->nombre }}</span></td>
                                <td>
                                    <span class="badge {{ $documento->extension === 'pdf' ? 'bg-danger' : 'bg-primary' }}">
                                        {{ strtoupper($documento->extension) }}
                                    </span>
                                </td>
                                <td>{{ $documento->tamanio_legible }}</td>
                                <td>{{ $documento->subidoPor->name ?? '—' }}</td>
                                <td>{{ $documento->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    @if ($documento->extension === 'pdf')
                                        <a href="{{ route('admin.documentos.preview', $documento) }}"
                                            class="btn btn-outline-info btn-sm" title="Ver" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.documentos.download', $documento) }}"
                                        class="btn btn-outline-primary btn-sm" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>

                                    @can('delete', $documento)
                                        <form action="{{ route('admin.documentos.destroy', $documento) }}" method="POST"
                                            style="display:inline-block;"
                                            onsubmit="return confirm('¿Eliminar este documento?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay documentos cargados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
