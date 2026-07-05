@extends('layouts.public')

@section('title', 'Novedades')

@section('content')
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-newspaper"></i> Guardias Finalizadas</h4>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Capitán de Servicio</th>
                            <th>Oficial de Día</th>
                            <th>Novedades</th>
                            <th class="text-center">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guardias as $guardia)
                            <tr>
                                <td>{{ $guardia->date->format('d/m/Y') }}</td>
                                <td>{{ $guardia->capitan->grade }} {{ $guardia->capitan->name }}
                                    {{ $guardia->capitan->last_name }}</td>
                                <td>{{ $guardia->oficial->grade }} {{ $guardia->oficial->name }}
                                    {{ $guardia->oficial->last_name }}</td>
                                <td>{{ $guardia->novedades()->count() }}</td>
                                <td class="text-center">
                                    <a href="{{ route('guardias-publicas.show', $guardia) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-list"></i> Ver novedad
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay guardias finalizadas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($guardias->hasPages())
                <div class="card-footer">
                    {{ $guardias->links() }}
                </div>
            @endif
        </div>
    </div>
@stop
