<div>
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h3 class="card-title mb-0">Historial de Entregas y Devoluciones</h3>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="filtroTipo" class="form-control">
                        <option value="">Todos los tipos</option>
                        <option value="entrega">Entrega</option>
                        <option value="devolucion">Devolución</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="filtroUbicacionId" class="form-control">
                        <option value="">Todas las ubicaciones</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-ops">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th class="text-center">Ítems</th>
                        <th>Usuario</th>
                        <th>Motivo</th>
                        <th class="text-right">Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entregas as $entrega)
                        <tr wire:key="entrega-{{ $entrega->id }}">
                            <td>{{ $entrega->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $entrega->esDevolucion() ? 'badge-info' : 'badge-success' }}">
                                    {{ $entrega->esDevolucion() ? 'Devolución' : 'Entrega' }}
                                </span>
                            </td>
                            <td>{{ $entrega->ubicacionOrigen->nombre ?? '—' }}</td>
                            <td>{{ $entrega->ubicacionDestino->nombre ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge badge-secondary">{{ $entrega->movimientos_count }}</span>
                            </td>
                            <td>{{ $entrega->usuario->name ?? '—' }}</td>
                            <td>{{ $entrega->motivo ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.inventario.entregas.comprobante', $entrega) }}"
                                   target="_blank" class="btn btn-outline-secondary btn-sm" title="Ver comprobante">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No hay entregas ni devoluciones registradas todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $entregas->links() }}
        </div>
    </div>
</div>