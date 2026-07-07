@extends('layouts.app')

@section('subtitle', 'Mantenimientos')
@section('content_header_title', 'Vehículos')
@section('content_header_subtitle', 'Mantenimientos - ' . $vehiculo->matricula)

@section('content_body')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tools"></i> Mantenimientos de {{ $vehiculo->matricula }}
            </h3>
            <div class="card-tools">
                @can('create', App\Models\MantenimientoVehiculo::class)
                    <a href="{{ route('admin.vehiculos.mantenimientos.create', $vehiculo) }}"
                       class="btn btn-outline-primary btn-sm"
                       style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                        <i class="fas fa-plus-circle"></i> Registrar Mantenimiento
                    </a>
                @endcan
                <a href="{{ route('admin.vehiculos.show', $vehiculo) }}"
                   class="btn btn-outline-secondary btn-sm"
                   style="background-color: rgba(108, 117, 125, 0.08); border-color: rgba(108, 117, 125, 0.25);">
                    <i class="fas fa-arrow-left"></i> Volver al vehículo
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Km</th>
                        <th>Descripción</th>
                        <th>Costo</th>
                        <th>Taller</th>
                        <th>Próximo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mantenimientos as $mantenimiento)
                        <tr>
                            <td>{{ $mantenimiento->fecha->format('d/m/Y') }}</td>
                            <td><span class="badge badge-secondary">{{ $mantenimiento->tipo_label }}</span></td>
                            <td>{{ $mantenimiento->kilometraje ?? '-' }}</td>
                            <td>{{ $mantenimiento->descripcion }}</td>
                            <td>{{ $mantenimiento->costo ? '$' . number_format($mantenimiento->costo, 2, ',', '.') : '-' }}</td>
                            <td>{{ $mantenimiento->taller ?? '-' }}</td>
                            <td>
                                @if($mantenimiento->proximo_mantenimiento_fecha)
                                    {{ $mantenimiento->proximo_mantenimiento_fecha->format('d/m/Y') }}
                                @elseif($mantenimiento->proximo_mantenimiento_km)
                                    {{ $mantenimiento->proximo_mantenimiento_km }} km
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex justify-content-center">
                                    @can('update', $mantenimiento)
                                        <a href="{{ route('admin.vehiculos.mantenimientos.edit', [$vehiculo, $mantenimiento]) }}"
                                           class="btn btn-outline-warning btn-xs mr-1"
                                           style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $mantenimiento)
                                        <form action="{{ route('admin.vehiculos.mantenimientos.destroy', [$vehiculo, $mantenimiento]) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este mantenimiento?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-xs"
                                                    style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-tools fa-2x d-block mb-2"></i>
                                No hay mantenimientos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mantenimientos->hasPages())
            <div class="card-footer">
                {{ $mantenimientos->links() }}
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