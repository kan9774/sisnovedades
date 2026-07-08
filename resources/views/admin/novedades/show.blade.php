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
                <div class="card-tools ml-2">
                    <!-- Botón Volver -->
                    <a href="{{ route('admin.guardias.show', $guardia) }}" class="btn btn-outline-secondary btn-sm mr-1"
                        style="background-color: rgba(108, 117, 125, 0.08);" aria-label="Volver a la guardia">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    @can('update', $novedad)
                        <!-- Botón Editar -->
                        <a href="{{ route('admin.guardias.novedades.edit', [$guardia, $novedad]) }}"
                            class="btn btn-outline-warning btn-sm mr-1" style="background-color: rgba(255, 193, 7, 0.08);"
                            aria-label="Editar novedad">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    @endcan

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
                        <strong>Hora:</strong><br>{{ $novedad->time }}
                    </div>
                </div>

                <!-- Segunda fila: Organismo, Oficina, Asunto -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>{{ $novedad->direction === 'Recibido' ? 'Quién expide' : 'Expedido' }}:</strong><br>
                        {{ $novedad->remitente() ?? '-' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Oficina:</strong><br>{{ $novedad->office ?? '-' }}
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
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-paperclip"></i> Adjuntos</h3>
            </div>
            <div class="card-body">

                @php
                    $puedeGestionarAdjuntos =
                        $guardia->status === 'open' &&
                        ($guardia->esMiembro(auth()->user()) || auth()->user()->isAdmin());
                @endphp

                {{-- Subir archivo — solo si tiene permiso --}}
                @if ($puedeGestionarAdjuntos)
                    <form action="{{ route('admin.adjuntos.store', [$guardia, $novedad]) }}" method="POST"
                        enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('archivo') is-invalid @enderror"
                                    name="archivo" id="archivo" accept=".pdf,.jpg,.jpeg,.png"
                                    {{ in_array($novedad->type, ['Fax', 'Correo Electrónico']) ? 'required' : '' }}>
                                <label class="custom-file-label" for="archivo">
                                    Seleccionar archivo (PDF, JPG, PNG — máx. 10MB)
                                </label>
                            </div>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Subir
                                </button>
                            </div>
                        </div>
                        @error('archivo')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                        @if (in_array($novedad->type, ['Fax', 'Correo Electrónico']))
                            <small class="text-muted">* Obligatorio para Fax y Correo Electrónico.</small>
                        @endif
                    </form>
                @endif

                {{-- Listado de adjuntos --}}
                @forelse($novedad->adjuntos as $adjunto)
                    <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                        <div>
                            @if ($adjunto->esPdf())
                                <i class="fas fa-file-pdf text-danger mr-2"></i>
                            @else
                                <i class="fas fa-file-image text-info mr-2"></i>
                            @endif
                            <strong>{{ $adjunto->file_name }}</strong>
                            <small class="text-muted ml-2">{{ $adjunto->tamanoLegible() }}</small>
                            <small class="text-muted ml-2">— {{ $adjunto->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <div>
                            <a href="{{ route('admin.adjuntos.download', [$guardia, $novedad, $adjunto]) }}"
                                class="btn btn-info btn-xs">
                                <i class="fas fa-download"></i>
                            </a>
                            @if ($puedeGestionarAdjuntos)
                                <form action="{{ route('admin.adjuntos.destroy', [$guardia, $novedad, $adjunto]) }}"
                                    method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar adjunto?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-xs">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No hay archivos adjuntos.</p>
                @endforelse
            </div>
        </div>
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
