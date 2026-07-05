@extends('layouts.public')

@section('title', 'Novedad #' . $novedad->id)

@section('content')
<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #0d6efd;">
            <h4 class="mb-0">
                <i class="fas fa-file-alt text-primary"></i> Novedad #{{ $novedad->id }}
                @php
                    $colores = [
                        'Rutinario'   => 'secondary',
                        'Prioritario' => 'primary',
                        'Urgente'     => 'warning',
                        'Destello'    => 'danger',
                    ];
                @endphp
                <span class="badge badge-{{ $colores[$novedad->clasification] ?? 'secondary' }} ml-2">
                    {{ $novedad->clasification }}
                </span>
            </h4>
            <div class="card-tools">
                <a href="{{ route('guardias-publicas.show', $guardia) }}"
                   class="btn btn-outline-secondary btn-sm"
                   style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);"
                   aria-label="Volver a la guardia">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <strong><i class="fas fa-tag mr-1"></i> Tipo:</strong>
                    <p class="mt-1">{{ $novedad->type }}</p>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-exchange-alt mr-1"></i> Dirección:</strong>
                    <p class="mt-1">
                        @if($novedad->direction === 'Recibido')
                            <span class="badge badge-success">Recibido</span>
                        @else
                            <span class="badge badge-warning">Expedido</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-hashtag mr-1"></i> Número:</strong>
                    <p class="mt-1">{{ $novedad->number }}</p>
                </div>
                <div class="col-md-3">
                    <strong><i class="fas fa-clock mr-1"></i> Hora:</strong>
                    <p class="mt-1">{{ \Carbon\Carbon::parse($novedad->time)->format('H:i') }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <strong><i class="fas fa-building mr-1"></i> {{ $novedad->direction === 'Recibido' ? 'Quién expide' : 'Destino' }}:</strong>
                    <p class="mt-1">{{ $novedad->direction === 'Recibido' ? ($novedad->organismo->name ?? '-') : ($novedad->destino ?? '-') }}</p>
                </div>
                <div class="col-md-4">
                    <strong><i class="fas fa-door-open mr-1"></i> Oficina:</strong>
                    <p class="mt-1">{{ $novedad->office ?? '-' }}</p>
                </div>
                <div class="col-md-4">
                    <strong><i class="fas fa-heading mr-1"></i> Asunto:</strong>
                    <p class="mt-1">{{ $novedad->affair ?? '-' }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <strong><i class="fas fa-file-alt mr-1"></i> Texto:</strong>
                    <div class="p-3 mt-1 bg-white border rounded shadow-sm" style="background-color: #f8f9fa;">
                        {{ $novedad->text }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Adjuntos --}}
    <div class="card shadow-sm mt-3">
        <div class="card-header" style="background: #f8f9fa;">
            <h5 class="mb-0">
                <i class="fas fa-paperclip text-primary"></i> Archivos adjuntos
            </h5>
        </div>
        <div class="card-body">
            @forelse($novedad->adjuntos as $adjunto)
                <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2" style="background: #f8f9fa; border-color: #dee2e6 !important;">
                    <div>
                        @if($adjunto->esPdf())
                            <i class="fas fa-file-pdf text-danger mr-2"></i>
                        @else
                            <i class="fas fa-file-image text-info mr-2"></i>
                        @endif
                        <strong>{{ $adjunto->file_name }}</strong>
                        <small class="text-muted ml-2">({{ $adjunto->tamanoLegible() }})</small>
                    </div>
                    <a href="{{ route('guardias-publicas.adjuntos.download', [$guardia, $novedad, $adjunto]) }}"
                       class="btn btn-outline-info btn-sm"
                       style="background-color: rgba(23, 162, 184, 0.08); border-color: rgba(23, 162, 184, 0.25);"
                       aria-label="Descargar archivo">
                        <i class="fas fa-download"></i> Descargar
                    </a>
                </div>
            @empty
                <p class="text-muted text-center py-3 mb-0">
                    <i class="fas fa-info-circle mr-1"></i> Esta novedad no tiene archivos adjuntos.
                </p>
            @endforelse
        </div>
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
    .badge.bg-success {
        background-color: #28a745 !important;
        color: white;
    }
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #212529;
    }
</style>
@endpush