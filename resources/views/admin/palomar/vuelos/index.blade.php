@extends('layouts.app')

@section('subtitle', 'Vuelos')
@section('content_header_title', 'Vuelos')
@section('content_header_subtitle', 'Listado general')

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
            <h3 class="card-title"><i class="fas fa-plane"></i> Vuelos registrados</h3>
            <div class="card-tools">
                <a href="{{ route('admin.vuelos.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-circle"></i> Registrar Vuelo
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <select name="paloma_id" class="form-control">
                            <option value="">Todas las palomas</option>
                            @foreach($palomas as $p)
                                <option value="{{ $p->id }}" {{ request('paloma_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->anilla }} - {{ $p->nombre ?? 'S/N' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
                        <a href="{{ route('admin.vuelos.index') }}" class="btn btn-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Palomas</th>
                        <th>Vel. media grupo</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vuelos as $vuelo)
                        <tr>
                            <td>{{ $vuelo->fecha->format('d/m/Y') }}</td>
                            <td>
                                @if ($vuelo->tipo === 'entrenamiento')
                                    <span class="badge bg-info"><i class="fas fa-running mr-1"></i> Entrenamiento</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="fas fa-trophy mr-1"></i> Competición</span>
                                @endif
                            </td>
                            <td>
                                @foreach($vuelo->palomas as $p)
                                    <span class="badge bg-secondary">{{ $p->anilla }}</span>
                                @endforeach
                            </td>
                            <td>{{ $vuelo->velocidad_promedio ?? '-' }}</td>
                            <td>
                                @if($vuelo->estado === 'en_curso')
                                    <span class="badge bg-warning text-dark">En curso</span>
                                @else
                                    <span class="badge bg-success">Finalizado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($vuelo->estado === 'en_curso')
                                    <a href="{{ route('admin.vuelos.resultados', $vuelo) }}" class="btn btn-outline-success btn-sm" title="Cargar resultados">
                                        <i class="fas fa-flag-checkered"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.vuelos.edit', $vuelo) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.vuelos.destroy', $vuelo) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Eliminar este vuelo?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No hay vuelos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $vuelos->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@stop