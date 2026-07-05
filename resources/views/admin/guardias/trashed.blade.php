@extends('layouts.app')

@section('subtitle', 'Papelera de Guardias')
@section('content_header_title', 'Guardias')
@section('content_header_subtitle', 'Papelera')

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

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-trash"></i> Guardias Eliminadas
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.guardias.index') }}"
                   class="btn btn-outline-secondary btn-sm"
                   style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                   aria-label="Volver al listado">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Capitán</th>
                        <th>Oficial de Día</th>
                        <th>Estado</th>
                        <th>Eliminado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guardias as $guardia)
                        <tr>
                            <td>{{ $guardia->date->format('d/m/Y') }}</td>
                            <td>{{ $guardia->capitan->grade ?? '' }} {{ $guardia->capitan->name ?? '' }}</td>
                            <td>{{ $guardia->oficial->grade ?? '' }} {{ $guardia->oficial->name ?? '' }}</td>
                            <td>
                                @if($guardia->status === 'open')
                                    <span class="badge badge-success">Abierta</span>
                                @else
                                    <span class="badge badge-danger">Cerrada</span>
                                @endif
                            </td>
                            <td>{{ $guardia->deleted_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    @can('restore', $guardia)
                                        <form action="{{ route('admin.guardias.restore', $guardia->id) }}"
                                              method="POST"
                                              class="d-inline mr-1"
                                              onsubmit="return confirm('¿Restaurar esta guardia?')">
                                            @csrf
                                            <button class="btn btn-outline-success btn-xs"
                                                    style="background-color: rgba(40, 167, 69, 0.08); border-color: rgba(40, 167, 69, 0.25);"
                                                    aria-label="Restaurar guardia">
                                                <i class="fas fa-undo"></i> Restaurar
                                            </button>
                                        </form>
                                    @endcan
                                    @can('forceDelete', $guardia)
                                        <form action="{{ route('admin.guardias.force-delete', $guardia->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Eliminar permanentemente esta guardia? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-xs"
                                                    style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                                    aria-label="Eliminar permanentemente">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-trash fa-2x d-block mb-2"></i>
                                No hay guardias en la papelera.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($guardias->hasPages())
            <div class="card-footer">
                {{ $guardias->links() }}
            </div>
        @endif
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