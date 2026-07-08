@extends('layouts.app')

@section('subtitle', 'Auditoría')
@section('content_header_title', 'Sistema')
@section('content_header_subtitle', 'Log de Actividad')

@section('content_body')
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Registro de actividad</h3>
            </div>
            <div class="card-body">

                <form method="GET" class="form-inline mb-3">
                    <select name="log_name" class="form-control mr-2 mb-2">
                        <option value="">-- Todas las entidades --</option>
                        @foreach ($logNames as $name)
                            <option value="{{ $name }}" {{ request('log_name') == $name ? 'selected' : '' }}>{{ ucfirst($name) }}</option>
                        @endforeach
                    </select>

                    <select name="event" class="form-control mr-2 mb-2">
                        <option value="">-- Todos los eventos --</option>
                        @foreach ($eventos as $evento)
                            <option value="{{ $evento }}" {{ request('event') == $evento ? 'selected' : '' }}>{{ ucfirst($evento) }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="desde" class="form-control mr-2 mb-2" value="{{ request('desde') }}">
                    <input type="date" name="hasta" class="form-control mr-2 mb-2" value="{{ request('hasta') }}">

                    <button type="submit" class="btn btn-outline-primary mb-2"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary mb-2 ml-2">Limpiar</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Entidad</th>
                                <th>Evento</th>
                                <th>Descripción</th>
                                <th>Usuario</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge badge-secondary">{{ $log->log_name }}</span></td>
                                    <td>{{ ucfirst($log->event) }}</td>
                                    <td>{{ $log->description }}</td>
                                    <td>{{ $log->causer?->name ?? 'Sistema' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" type="button"
                                            data-toggle="collapse" data-target="#detalle-{{ $log->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="detalle-{{ $log->id }}">
                                    <td colspan="6">
                                        <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $logs->links() }}
            </div>
        </div>
    </div>
@stop