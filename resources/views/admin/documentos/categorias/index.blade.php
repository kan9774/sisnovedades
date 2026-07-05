@extends('layouts.app')

@section('subtitle', 'Categorías de documentos')
@section('content_header_title', 'Categorías')
@section('content_header_subtitle', 'Gestión de categorías de documentos')

@section('content_body')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-folder"></i> Categorías</h3>
            <div class="card-tools">
                <a href="{{ route('admin.documentos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver a documentos
                </a>
                @can('create', App\Models\CategoriaDocumento::class)
                    <a href="{{ route('admin.documentos.categorias.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus-circle"></i> Nueva categoría
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Documentos</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorias as $categoria)
                        <tr>
                            <td>{{ $categoria->nombre }}</td>
                            <td>{{ $categoria->descripcion ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $categoria->documentos_count }}</span></td>
                            <td class="text-center">
                                @can('update', App\Models\CategoriaDocumento::class)
                                    <a href="{{ route('admin.documentos.categorias.edit', $categoria) }}"
                                       class="btn btn-outline-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('delete', App\Models\CategoriaDocumento::class)
                                    <form action="{{ route('admin.documentos.categorias.destroy', $categoria) }}"
                                          method="POST" style="display:inline-block;"
                                          onsubmit="return confirm('¿Eliminar esta categoría?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">No hay categorías cargadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop