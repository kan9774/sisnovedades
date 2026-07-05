@extends('layouts.app')

@section('subtitle', 'Papelera de documentos')
@section('content_header_title', 'Papelera')
@section('content_header_subtitle', 'Documentos eliminados')

@section('content_body')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-trash"></i> Documentos eliminados</h3>
            <div class="card-tools">
                <a href="{{ route('admin.documentos.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Subido por</th>
                        <th>Eliminado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documentos as $documento)
                        <tr>
                            <td>{{ $documento->titulo }}</td>
                            <td><span class="badge bg-secondary">{{ $documento->categoria->nombre }}</span></td>
                            <td>{{ $documento->subidoPor->name ?? '—' }}</td>
                            <td>{{ $documento->deleted_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                @can('restore', $documento)
                                    <form action="{{ route('admin.documentos.restore', $documento->id) }}"
                                          method="POST" style="display:inline-block;">
                                        @csrf
                                        <button class="btn btn-outline-success btn-sm" title="Restaurar">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                @endcan
                                @can('forceDelete', $documento)
                                    <form action="{{ route('admin.documentos.force-delete', $documento->id) }}"
                                          method="POST" style="display:inline-block;"
                                          onsubmit="return confirm('Esto elimina el archivo de forma permanente. ¿Continuar?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" title="Eliminar definitivamente">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">La papelera está vacía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop