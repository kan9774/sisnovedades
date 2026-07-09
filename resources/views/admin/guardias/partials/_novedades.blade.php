<div class="d-flex justify-content-end mb-2">
    @if ($guardia->status === 'open' && $puedeOperarGuardia)
        <a href="{{ route('admin.guardias.novedades.create', $guardia) }}" class="btn btn-outline-info btn-sm">
            <i class="fas fa-plus-circle"></i> Registrar Tráfico
        </a>
    @endif
</div>

<table class="table table-striped table-hover mb-0" style="width: 100%">
    <thead class="thead-dark">
        <tr>
            <th>#</th>
            <th>Hora</th>
            <th>Tipo</th>
            <th>Dirección</th>
            <th>Número</th>
            <th>Asunto</th>
            <th>Clasificación</th>
            <th>Escribiente</th>
            <th>Estado</th>
            <th class="text-center">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($novedades as $novedad)
            <tr>
                <td>{{ $loop->iteration + ($novedades->currentPage() - 1) * $novedades->perPage() }}</td>
                <td>{{ $novedad->time }}</td>
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
                        $colores = ['Rutinario' => 'secondary', 'Prioritario' => 'primary', 'Urgente' => 'warning', 'Destello' => 'danger'];
                    @endphp
                    <span class="badge badge-{{ $colores[$novedad->clasification] ?? 'secondary' }}">
                        {{ $novedad->clasification }}
                    </span>
                </td>
                <td>{{ $novedad->escribiente->name ?? '-' }}</td>
                <td>
                    <livewire:estado-novedad :novedad="$novedad" :guardia="$guardia" :compacto="true"
                        :key="'estado-novedad-tabla-' . $novedad->id" />
                </td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <a href="{{ route('admin.guardias.novedades.show', [$guardia, $novedad]) }}"
                            class="btn btn-outline-info btn-xs mr-1" style="background-color: rgba(23, 162, 184, 0.1);"
                            aria-label="Ver novedad">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('update', $novedad)
                            <a href="{{ route('admin.guardias.novedades.edit', [$guardia, $novedad]) }}"
                                class="btn btn-outline-warning btn-xs mr-1" style="background-color: rgba(255, 193, 7, 0.1);"
                                aria-label="Editar novedad">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endcan
                        @can('delete', $novedad)
                            <form action="{{ route('admin.guardias.novedades.destroy', [$guardia, $novedad]) }}"
                                method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar novedad?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-xs" style="background-color: rgba(220, 53, 69, 0.1);"
                                    aria-label="Eliminar novedad">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10">
                    <div class="text-center text-muted py-4">
                        <i class="fa-solid fa-tower-cell fa-2x d-block mb-2"></i>
                        No hay tráficos registrados en esta guardia.
                        @if ($guardia->status === 'open' && $puedeOperarGuardia)
                            <br>
                            <a href="{{ route('admin.guardias.novedades.create', $guardia) }}"
                                class="btn btn-outline-primary btn-sm mt-2"
                                style="background-color: rgba(0, 123, 255, 0.08); border-color: rgba(0, 123, 255, 0.25);">
                                <i class="fas fa-plus-circle"></i> Registrar el primer tráfico
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@if ($novedades->hasPages())
    <div class="mt-3">{{ $novedades->links() }}</div>
@endif