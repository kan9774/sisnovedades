@extends('layouts.app')

@section('subtitle', 'Novedades del día')
@section('content_header_title', 'Novedades')
@section('content_header_subtitle', 'Vista general del día')

@section('content_body')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(!$guardia)
        <div class="alert alert-warning shadow-sm d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle mr-3 fa-lg"></i>
            <div>
                No hay guardia abierta hoy.
                @can('create', App\Models\Guard::class)
                    <a href="{{ route('admin.guardias.create') }}" class="alert-link font-weight-bold ml-1">Abrir guardia del día</a>
                @endcan
            </div>
        </div>
    @else
        {{-- Resumen de la guardia --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-info shadow-sm">
                    <div class="inner">
                        <h4>{{ $guardia->date->format('d/m/Y') }}</h4>
                        <p>Guardia del día</p>
                        @if($guardia->status === 'open')
                            <span class="badge badge-light text-success">Abierta</span>
                        @else
                            <span class="badge badge-light text-warning">Cerrada</span>
                        @endif
                    </div>
                    <div class="icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-warning shadow-sm">
                    <div class="inner">
                        <h5>{{ $guardia->capitan->grade }} {{ $guardia->capitan->name }}</h5>
                        <p>Capitán de Servicio</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-success shadow-sm">
                    <div class="inner">
                        <h5>{{ $guardia->oficial->grade }} {{ $guardia->oficial->name }}</h5>
                        <p>Oficial de Día</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-gradient-danger shadow-sm">
                    <div class="inner">
                        <h4>{{ $guardia->novedades->count() }}</h4>
                        <p>Total Novedades</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de novedades --}}
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-ul mr-2"></i> Novedades del día
                </h3>
                <div class="card-tools">
                    @can('create', App\Models\News::class)
                        @if($guardia->status === 'open')
                            <a href="{{ route('admin.guardias.novedades.create', $guardia) }}"
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Nueva Novedad
                            </a>
                        @endif
                    @endcan
                    <a href="{{ route('admin.guardias.show', $guardia) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-eye mr-1"></i> Ver Guardia
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Hora</th>
                                <th>Tipo</th>
                                <th>Dirección</th>
                                <th>Número</th>
                                <th>Asunto</th>
                                <th>Clasificación</th>
                                <th>Escribiente</th>
                                <th class="text-center" style="width: 120px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guardia->novedades as $novedad)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><span class="badge badge-light">{{ \Carbon\Carbon::parse($novedad->time)->format('H:i') }}</span></td>
                                    <td>{{ $novedad->type }}</td>
                                    <td>
                                        @if($novedad->direction === 'Recibido')
                                            <span class="badge badge-success badge-pill">Recibido</span>
                                        @else
                                            <span class="badge badge-warning badge-pill">Expedido</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $novedad->number }}</code></td>
                                    <td>{{ Str::limit($novedad->affair, 30) }}</td>
                                    <td>
                                        @php
                                            $colores = [
                                                'Rutinario'   => 'secondary',
                                                'Prioritario' => 'info',
                                                'Urgente'     => 'warning',
                                                'Destello'    => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge badge-{{ $colores[$novedad->clasification] ?? 'secondary' }} badge-pill">
                                            {{ $novedad->clasification }}
                                        </span>
                                    </td>
                                    <td>{{ $novedad->escribiente->name ?? '-' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.guardias.novedades.show', [$guardia, $novedad]) }}"
                                               class="btn btn-outline-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $novedad)
                                                <a href="{{ route('admin.guardias.novedades.edit', [$guardia, $novedad]) }}"
                                                   class="btn btn-outline-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $novedad)
                                                <form action="{{ route('admin.guardias.novedades.destroy', [$guardia, $novedad]) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Eliminar esta novedad?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No hay novedades registradas hoy.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                <small class="text-muted">Mostrando {{ $guardia->novedades->count() }} novedades</small>
            </div>
        </div>
    @endif
</div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        // Auto-cerrar alertas después de 5 segundos
        $('.alert').delay(5000).slideUp(500);
    });
</script>
@endpush