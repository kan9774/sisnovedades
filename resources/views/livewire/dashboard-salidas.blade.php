<div class="card card-outline card-primary" wire:poll.10000ms="loadSalidas">
    <div class="card-header border-transparent">
        <h3 class="card-title"><i class="fas fa-truck-moving mr-1"></i> Últimas Salidas de Vehículos</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" wire:click="loadSalidas">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0 table-hover text-sm">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Conductor</th>
                        <th>Hora Salida</th>
                        <th>Hora Llegada</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimasSalidas as $salida)
                    <tr wire:key="salida-{{ $salida->id }}">
                        <td><strong>{{ $salida->vehiculo?->matricula }}</strong></td>
                        <td>{{ $salida->conductor?->nombre_completo }}</td>
                        <td>{{ $salida->hora_sale?->format('H:i') ?? '--:--' }}</td>
                        <td>{{ $salida->hora_entra?->format('H:i') ?? '--:--' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No hay salidas registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
