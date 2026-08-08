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

    <x-ops-card title="Vuelos registrados" icon="plane" eyebrow="{{ $vuelos->count() }} registros">
        <x-slot name="actions">
            <a href="{{ route('admin.vuelos.create') }}" class="btn-ops btn-ops-primary btn-sm">
                <i class="fas fa-plus-circle"></i> Registrar Vuelo
            </a>
        </x-slot>

        <form method="GET" class="mb-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <select name="paloma_id" class="form-control">
                        <option value="">Todas las palomas</option>
                        @foreach($palomas as $p)
                            <option value="{{ $p->id }}" {{ request('paloma_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->anilla }} - {{ $p->nombre ?? 'S/N' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn-ops btn-ops-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="{{ route('admin.vuelos.index') }}" class="btn-ops btn-ops-secondary btn-sm">Limpiar</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-ops-hover align-middle">
                <thead class="thead-ops">
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
                                    <span class="badge-ops badge-ops-info"><i class="fas fa-running mr-1"></i> Entrenamiento</span>
                                @else
                                    <span class="badge-ops badge-ops-warning"><i class="fas fa-trophy mr-1"></i> Competición</span>
                                @endif
                            </td>
                            <td>
                                @foreach($vuelo->palomas as $p)
                                    <span class="badge-ops badge-ops-secondary">{{ $p->anilla }}</span>
                                @endforeach
                            </td>
                            <td>{{ $vuelo->velocidad_promedio ?? '-' }}</td>
                            <td>
                                @if($vuelo->estado === 'en_curso')
                                    <span class="badge-ops badge-ops-warning">En curso</span>
                                @else
                                    <span class="badge-ops badge-ops-success">Finalizado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="ops-actions">
                                    @if($vuelo->estado === 'en_curso')
                                        <a href="{{ route('admin.vuelos.resultados', $vuelo) }}" class="btn-ops btn-ops-success btn-ops-icon btn-ops-icon--sm" title="Cargar resultados">
                                            <i class="fas fa-flag-checkered"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.vuelos.edit', $vuelo) }}" class="btn-ops btn-ops-warning btn-ops-icon btn-ops-icon--sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.vuelos.destroy', $vuelo) }}" method="POST" class="d-inline-block" onsubmit="return confirm('¿Eliminar este vuelo?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-ops btn-ops-danger btn-ops-icon btn-ops-icon--sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-3 text-muted">No hay vuelos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-slot name="footer">
            {{ $vuelos->appends(request()->query())->links() }}
        </x-slot>
    </x-ops-card>
</div>
@stop