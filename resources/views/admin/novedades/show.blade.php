@extends('layouts.app')

@section('subtitle', 'Novedad #' . $novedad->id)
@section('content_header_title', 'Novedades')
@section('content_header_subtitle', 'Detalle')

@section('content_body')
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card card-primary card-outline">
            <div class="card-header ">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i> Novedad #{{ $novedad->id }}
                    @php
                        $colores = [
                            'Rutinario' => 'info',
                            'Prioritario' => 'primary',
                            'Urgente' => 'warning',
                            'Destello' => 'danger',
                        ];
                    @endphp
                    <span class="badge badge-{{ $colores[$novedad->clasification] ?? 'secondary' }} ml-2">
                        {{ $novedad->clasification }}
                    </span>
                </h3>
                <livewire:estado-novedad :novedad="$novedad" :guardia="$guardia" :key="'estado-novedad-' . $novedad->id" />
                <div class="card-tools ml-2">
                    <!-- Botón Volver -->
                    <a href="{{ route('admin.guardias.show', $guardia) }}" class="btn btn-outline-secondary btn-sm mr-1"
                        style="background-color: rgba(108, 117, 125, 0.08);" aria-label="Volver a la guardia">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <livewire:editar-novedad-modal :novedad="$novedad" :guardia="$guardia"
                        :key="'editar-novedad-modal-' . $novedad->id" />

                    @can('delete', $novedad)
                        <!-- Botón Eliminar -->
                        <form action="{{ route('admin.guardias.novedades.destroy', [$guardia, $novedad]) }}" method="POST"
                            class="d-inline" onsubmit="return confirm('¿Eliminar esta novedad?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm" style="background-color: rgba(220, 53, 69, 0.08);"
                                aria-label="Eliminar novedad">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <!-- Primera fila: Tipo, Dirección, Número, Hora -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Tipo:</strong><br>{{ $novedad->type }}
                    </div>
                    <div class="col-md-3">
                        <strong>Dirección:</strong><br>
                        @if ($novedad->direction === 'Recibido')
                            <span class="badge badge-success">Recibido</span>
                        @else
                            <span class="badge badge-warning">Expedido</span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <strong>Número:</strong><br>{{ $novedad->number }}
                    </div>
                    <div class="col-md-3">
                        <strong>Hora:</strong><br><span class="badge badge-info"> {{ $novedad->time->format('H:i') }} </span>
                    </div>
                </div>

                <!-- Segunda fila: Organismo, Oficina, Asunto -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>{{ $novedad->direction === 'Recibido' ? 'Quién expide' : 'Expedido' }}:</strong><br>
                        {{ $novedad->remitente() ?? '-' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Oficina:</strong><br>{{ $novedad->oficina->nombre ?? '-' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Asunto:</strong><br>{{ $novedad->affair ?? '-' }}
                    </div>
                </div>

                <!-- Texto de la novedad -->
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Texto:</strong>
                        <div class="p-3 mt-1 bg-white border rounded shadow-sm">
                            {{ $novedad->text }}
                        </div>
                    </div>
                </div>

                <!-- Registrado por -->
                <div class="row">
                    <div class="col-12 text-muted small">
                        <strong>Registrado por:</strong>
                        {{ $novedad->escribiente->grade ?? '' }} {{ $novedad->escribiente->name ?? '' }}
                        {{ $novedad->escribiente->last_name ?? '' }}
                        — {{ $novedad->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Historial de cambios --}}
        @if ($novedad->logs->isNotEmpty())
            <div class="card card-outline collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> Historial</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fa-solid fa-plus-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Acción</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($novedad->logs as $log)
                                <tr>
                                    <td>{{ ucfirst($log->event ?? $log->description) }}</td>
                                    <td>{{ $log->causer->name ?? 'Sistema' }} {{ $log->causer->last_name ?? '' }}</td>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        {{-- Adjuntos --}}
        <livewire:gestion-adjuntos :novedad="$novedad" :guardia="$guardia" :key="'adjuntos-' . $novedad->id" />
    </div>
@stop
@push('js')
    <script>
        $('#archivo').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').text(fileName || 'Seleccionar archivo');
        });
    </script>
@endpush