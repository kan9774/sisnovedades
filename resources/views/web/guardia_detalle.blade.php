@extends('layouts.public')

@section('title', 'Guardia ' . $guardia->date->format('d/m/Y'))

@section('content')
    <div class="container mt-4">

        {{-- Info guardia --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #0d6efd;">
                <h4 class="mb-0">
                    <i class="fas fa-shield-alt text-primary"></i>
                    Guardia del {{ $guardia->date->format('d/m/Y') }}
                    <span class="badge bg-secondary ml-2">Cerrada</span>
                </h4>
                <div class="card-tools">

                    <a href="{{ route('novedades-publicas') }}" class="btn btn-outline-secondary btn-sm"
                        style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                        aria-label="Volver al listado">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <strong><i class="fas fa-user-tie mr-1"></i> Capitán de Servicio:</strong>
                        <p class="mt-1 mb-0">
                            {{ $guardia->capitan->grade }} {{ $guardia->capitan->name }} {{ $guardia->capitan->last_name }}
                        </p>
                    </div>
                    <div class="col-md-3 mb-2">
                        <strong><i class="fas fa-user-shield mr-1"></i> Oficial de Día:</strong>
                        <p class="mt-1 mb-0">
                            {{ $guardia->oficial->grade }} {{ $guardia->oficial->name }} {{ $guardia->oficial->last_name }}
                        </p>
                    </div>
                    <div class="col-md-3 mb-2">
                        <strong><i class="fas fa-file-alt mr-1"></i> Total novedades:</strong>
                        <p class="mt-1 mb-0">
                            <span class="badge bg-primary">{{ $guardia->novedades->count() }}</span>
                        </p>
                    </div>
                    <div class="col-md-3 mb-2">
                        <strong><i class="fas fa-user-tie mr-1"></i> Imprimir Novedad:</strong>
                        <p class="mt-1 mb-0">
                            <a href="{{ route('admin.guardias.pdf', $guardia) }}" class="btn btn-outline-danger btn-sm"
                                style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                target="_blank" aria-label="Descargar PDF">
                                <i class="fas fa-file-pdf"></i> Descarga r PDF
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de novedades --}}
        <div class="card shadow-sm">
            <div class="card-header" style="background: #f8f9fa;">
                <h5 class="mb-0">
                    <i class="fas fa-list text-primary"></i> Novedades
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Hora</th>
                                <th>Tipo</th>
                                <th>Dirección</th>
                                <th>Número</th>
                                <th>Asunto</th>
                                <th>Clasificación</th>
                                <th>Adjuntos</th>
                                <th class="text-center">Adjuntos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guardia->novedades as $novedad)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($novedad->time)->format('H:i') }}</td>
                                    <td>{{ $novedad->type }}</td>
                                    <td>
                                        @if ($novedad->direction === 'Recibido')
                                            <span class="badge badge-success">Recibido</span>
                                        @else
                                            <span class="badge badge-warning">Expedido</span>
                                        @endif
                                    </td>
                                    <td>{{ $novedad->number }}</td>
                                    <td>{{ Str::limit($novedad->affair, 40) }}</td>
                                    <td>
                                        @php
                                            $colores = [
                                                'Rutinario' => 'secondary',
                                                'Prioritario' => 'primary',
                                                'Urgente' => 'warning',
                                                'Destello' => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge badge-{{ $colores[$novedad->clasification] ?? 'secondary' }}">
                                            {{ $novedad->clasification }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($novedad->adjuntos->count() > 0)
                                            <i class="fas fa-paperclip"></i> {{ $novedad->adjuntos->count() }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    @forelse ($novedad->adjuntos as $adjunto)
                                        <td class="text-center align-middle">
                                            <a href="{{ route('guardias-publicas.adjuntos.view', [$guardia, $novedad, $adjunto]) }}"
                                                class="btn btn-outline-info btn-sm"
                                                style="background-color: rgba(23, 162, 184, 0.08); border-color: rgba(23, 162, 184, 0.25);"
                                                aria-label="Descargar archivo"
                                                target="_blank">
                                                <i class="fas fa-paperclip"></i> Ver adjuntos
                                            </a>
                                        @empty
                                            <p class="text-muted text-center py-3 mb-0">
                                                <i class="fas fa-info-circle mr-1"></i> Esta novedad no tiene archivos adjuntos.
                                            </p>
                                        @endforelse
                                        </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No hay novedades registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Paginación ELIMINADA --}}
        </div>
    </div>
@stop

@push('css')
    <style>
        .badge.bg-secondary {
            background-color: #6c757d !important;
            color: white;
        }

        .badge.bg-primary {
            background-color: #0d6efd !important;
            color: white;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .gap-1>*+* {
            margin-left: 0.25rem;
        }
    </style>
@endpush
