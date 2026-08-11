<div>
    <x-ops-card title="Registro de actividad" icon="history" eyebrow="{{ $logs->total() ?? $logs->count() }} registros">
        <x-slot name="actions">
            <button wire:click="limpiarFiltros" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-times"></i> Limpiar filtros
            </button>
        </x-slot>

        {{-- FILTROS --}}
        <div class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <select wire:model.live="logName" class="form-control form-control-sm">
                        <option value="">-- Todas las entidades --</option>
                        @foreach ($logNames as $name)
                            <option value="{{ $name }}">{{ ucfirst($name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="event" class="form-control form-control-sm">
                        <option value="">-- Todos los eventos --</option>
                        @foreach ($eventos as $evento)
                            <option value="{{ $evento }}">{{ ucfirst($evento) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" wire:model.live="desde" class="form-control form-control-sm" placeholder="Desde">
                </div>
                <div class="col-md-2">
                    <input type="date" wire:model.live="hasta" class="form-control form-control-sm" placeholder="Hasta">
                </div>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 130px;">Fecha</th>
                        <th style="width: 120px;">Entidad</th>
                        <th style="width: 100px;">Evento</th>
                        <th>Usuario</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr wire:key="log-{{ $log->id }}">
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-secondary">{{ $log->log_name }}</span></td>
                            <td>{{ ucfirst($log->event) }}</td>
                            <td>{{ $log->causer?->name ?? 'Sistema' }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info"
                                    data-toggle="collapse"
                                    data-target="#props{{ $log->id }}"
                                    title="Ver detalles">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="props{{ $log->id }}">
                            <td colspan="5">
                                <div class="mt-2 mb-0">
                                    <strong>Propiedades (old/new):</strong>
                                    <pre class="mb-0" style="white-space: pre-wrap; background: #f8f9fa; border-radius: 6px; padding: 1rem; max-height: 400px; overflow-y: auto;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($logs->hasPages())
            <div class="pt-3">
                {{ $logs->links() }}
            </div>
        @endif
    </x-ops-card>
</div>
