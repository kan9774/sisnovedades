<div>
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.400ms="busqueda"
                           class="form-control" placeholder="Buscar por ítem o código...">
                </div>
                <div class="col-md-6">
                    <select wire:model.live="filtroUbicacionId" class="form-control">
                        <option value="">Todas las ubicaciones (personas / oficinas / vehículos)</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Ítem</th>
                        <th>En poder de</th>
                        <th>N° Serie</th>
                        <th class="text-center">Cantidad</th>
                        <th>Venció el</th>
                        <th class="text-center">Hace</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registros as $r)
                        <tr class="table-danger">
                            <td>{{ $r['item']->codigo }} — {{ $r['item']->nombre }}</td>
                            <td>{{ $r['ubicacion']->nombre ?? '—' }}</td>
                            <td>{{ $r['numeroSerie'] ?? '—' }}</td>
                            <td class="text-center">{{ $r['cantidad'] }}</td>
                            <td>{{ $r['vencimiento']->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <span class="badge badge-danger">{{ $r['vencimiento']->diffForHumans(null, true) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hay ítems vencidos en poder de terceros. 🎉
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>