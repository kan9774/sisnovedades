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

    <div class="card card-outline-ops">
        <div class="card-header-ops">
            <div class="card-header-ops__title-wrap">
                <h3 class="card-title-ops mb-0">
                    <i class="fas fa-tools"></i> Mantenimientos de {{ $vehiculo->matricula }}
                </h3>
                <span class="card-header-ops__eyebrow">{{ $mantenimientos->count() }} registros</span>
            </div>
            <div class="card-tools">
                @can('create', App\Models\MantenimientoVehiculo::class)
                    <a href="{{ route('admin.vehiculos.mantenimientos.create', $vehiculo) }}"
                       class="btn-ops btn-ops-primary btn-sm">
                        <i class="fas fa-plus-circle"></i> Registrar Mantenimiento
                    </a>
                @endcan
                <a href="{{ route('admin.vehiculos.show', $vehiculo) }}"
                   class="btn-ops btn-ops-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver al vehículo
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-ops">
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
                            <td><span class="badge-ops badge-ops-secondary">{{ $mantenimiento->tipo_label }}</span></td>
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
                                    @can('delete', $mantenimiento)
                                        <form action="{{ route('admin.vehiculos.mantenimientos.destroy', [$vehiculo, $mantenimiento]) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('¿Eliminar este mantenimiento?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-ops btn-ops-danger btn-xs">
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