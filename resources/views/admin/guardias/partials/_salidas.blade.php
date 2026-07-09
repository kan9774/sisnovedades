<div class="d-flex justify-content-end mb-2">
    @if ($guardia->status === 'open' && $puedeOperarGuardia)
        <a href="{{ route('admin.guardias.salidas.create', $guardia) }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-plus-circle"></i> Registrar Salida
        </a>
    @endif
</div>

@if ($salidas->total() > 0)
    <table class="table table-striped table-hover mb-0" style="width: 100%">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Vehículo</th>
                <th>Conductor</th>
                <th>Combustible</th>
                <th>Hora Sale</th>
                <th>Hora Entra</th>
                <th>Km</th>
                <th>Litros</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salidas as $index => $salida)
                <tr>
                    <td>{{ $index + 1 + ($salidas->currentPage() - 1) * $salidas->perPage() }}</td>
                    <td>
                        @if ($salida->vehiculo)
                            <strong>{{ $salida->vehiculo->matricula }}</strong>
                            @if ($salida->vehiculo->sin_cuentakilometros)
                                <span class="badge badge-danger badge-pill">S/C</span>
                            @endif
                        @else
                            <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Vehículo eliminado</span>
                        @endif
                    </td>
                    <td>
                        @if ($salida->conductor)
                            {{ $salida->conductor->nombre_visible }}
                        @else
                            <span class="text-danger">Conductor eliminado</span>
                        @endif
                    </td>
                    <td>
                        @if ($salida->tipo_combustible === 'gas_oil')
                            <span class="badge badge-warning">Gas Oil</span>
                        @elseif($salida->tipo_combustible === 'nafta')
                            <span class="badge badge-info">Nafta</span>
                        @else
                            <span class="badge badge-secondary">{{ $salida->tipo_combustible }}</span>
                        @endif
                    </td>
                    <td>{{ $salida->hora_sale?->format('H:i') }}</td>
                    <td>{{ $salida->hora_entra?->format('H:i') ?? '-' }}</td>
                    <td>{{ $salida->kms_recorridos ?? '-' }}</td>
                    <td>{{ $salida->litros ? number_format($salida->litros, 2) : '-' }}</td>
                    <td class="text-center align-middle">
                        <div class="d-flex justify-content-center">
                            @can('update', $salida)
                                <a href="{{ route('admin.guardias.salidas.edit', [$guardia, $salida]) }}"
                                    class="btn btn-outline-warning btn-xs mr-1"
                                    style="background-color: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.25);"
                                    aria-label="Editar salida">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @can('delete', $salida)
                                <form action="{{ route('admin.guardias.salidas.destroy', [$guardia, $salida]) }}"
                                    method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta salida?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-xs"
                                        style="background-color: rgba(220, 53, 69, 0.08); border-color: rgba(220, 53, 69, 0.25);"
                                        aria-label="Eliminar salida">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
        @if ($resumenCombustible->isNotEmpty())
            <tfoot>
                @foreach ($resumenCombustible as $resumen)
                    <tr class="font-weight-bold" style="background: #f8f9fa;">
                        <td colspan="6" class="text-right">
                            TOTAL
                            @if ($resumen->tipo_combustible === 'gas_oil')
                                <span class="badge badge-warning">Gas Oil</span>
                            @elseif ($resumen->tipo_combustible === 'nafta')
                                <span class="badge badge-info">Nafta</span>
                            @else
                                <span class="badge badge-secondary">{{ $resumen->tipo_combustible }}</span>
                            @endif :
                        </td>
                        <td>{{ $resumen->total_kms ?? 0 }}</td>
                        <td>{{ number_format($resumen->total_litros ?? 0, 2) }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tfoot>
        @endif
    </table>

    @if ($salidas->hasPages())
        <div class="mt-3">{{ $salidas->links() }}</div>
    @endif
@else
    <div class="text-center text-muted py-4">
        <i class="fas fa-truck fa-2x d-block mb-2"></i>
        No hay salidas de vehículos registradas en esta guardia.
        @if ($guardia->status === 'open' && $puedeOperarGuardia)
            <br>
            <a href="{{ route('admin.guardias.salidas.create', $guardia) }}"
                class="btn btn-outline-primary btn-sm mt-2"
                style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                <i class="fas fa-plus-circle"></i> Registrar la primera salida
            </a>
        @endif
    </div>
@endif