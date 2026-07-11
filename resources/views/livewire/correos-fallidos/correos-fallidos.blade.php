<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted small mb-0">
            Correos que no pudieron entregarse para esta guardia.
        </p>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="soloPendientes-{{ $guardia->id }}"
                wire:model.live="soloPendientes">
            <label class="form-check-label" for="soloPendientes-{{ $guardia->id }}">Solo pendientes</label>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Fecha del fallo</th>
                    <th>Destinatario</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->fallos as $fallo)
                    <tr>
                        <td class="small">{{ \Carbon\Carbon::parse($fallo->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="small">{{ $fallo->email }}</td>
                        <td class="small text-muted" style="max-width: 380px;">
                            {{ Str::limit($fallo->motivo, 100) }}
                        </td>
                        <td>
                            @if ($fallo->resuelto_at)
                                <span class="badge badge-success">Resuelto</span>
                            @else
                                <span class="badge badge-danger">Pendiente</span>
                            @endif
                        </td>
                        <td class="text-right">
                            @unless ($fallo->resuelto_at)
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    wire:click="reintentar({{ $fallo->id }})"
                                    wire:confirm="¿Reintentar el envío a {{ $fallo->email }}?">
                                    <i class="fas fa-redo"></i> Reintentar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    wire:click="marcarResuelto({{ $fallo->id }})">
                                    <i class="fas fa-check"></i> Marcar resuelto
                                </button>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No hay correos fallidos {{ $soloPendientes ? 'pendientes' : 'registrados' }} para esta guardia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>