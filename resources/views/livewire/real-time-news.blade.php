<div class="card card-outline card-dark" wire:poll.15000ms="refreshNews">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history mr-1"></i> Diario de Novedades (Actualizado en Tiempo Real)</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" wire:click="refreshNews">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        @if($guardia)
            <div class="row">
                {{-- COLUMNA: RECIBIDOS --}}
                <div class="col-md-6">
                    <h5 class="text-center bg-info text-white py-1 mb-0">
                        <i class="fas fa-inbox mr-1"></i> Recibidos
                    </h5>
                    <ul class="products-list product-list-in-card pl-2 pr-2" style="max-height: 300px; overflow-y: auto;">
                        @forelse($recibidos as $novedad)
                            <li class="item" wire:key="recibido-{{ $novedad->id }}">
                                <div class="product-info ml-2">
                                    <div>
                                        <strong>N° {{ $novedad->number }}</strong>
                                        <span class="badge badge-{{ $novedad->esUrgente() ? 'danger' : 'info' }} float-right">{{ $novedad->clasification }}</span>
                                    </div>
                                    <span class="text-xs d-block"><strong>Destinatario:</strong> {{ $novedad->organismo?->name ?? 'Sin especificar' }}</span>
                                    <span class="text-xs d-block"><strong>Oficina:</strong> {{ $novedad->oficina?->nombre ?? 'N/A' }}</span>
                                    <span class="text-xs d-block"><strong>Asunto:</strong> {{ $novedad->affair ?? 'Sin asunto' }}</span>
                                    <span class="text-xs text-muted"><strong>{{ $novedad->time?->format('H:i') ?? '--:--' }} hs</strong></span>
                                </div>
                            </li>
                        @empty
                            <li class="item text-center text-muted py-3">
                                <i class="fas fa-check-circle text-success mr-1"></i> Sin recibidos.
                            </li>
                        @endforelse
                    </ul>
                </div>

                {{-- COLUMNA: EXPEDIDOS --}}
                <div class="col-md-6">
                    <h5 class="text-center bg-success text-white py-1 mb-0">
                        <i class="fas fa-paper-plane mr-1"></i> Expedidos
                    </h5>
                    <ul class="products-list product-list-in-card pl-2 pr-2" style="max-height: 300px; overflow-y: auto;">
                        @forelse($expedidos as $novedad)
                            <li class="item" wire:key="expedido-{{ $novedad->id }}">
                                <div class="product-info ml-2">
                                    <div>
                                        <strong>N° {{ $novedad->number }}</strong>
                                        <span class="badge badge-{{ $novedad->esUrgente() ? 'danger' : 'success' }} float-right">{{ $novedad->clasification }}</span>
                                    </div>
                                    <span class="text-xs d-block"><strong>Destinatario:</strong> {{ $novedad->destino ?? 'Sin especificar' }}</span>
                                    <span class="text-xs d-block"><strong>Oficina:</strong> {{ $novedad->oficina?->nombre ?? 'N/A' }}</span>
                                    <span class="text-xs d-block"><strong>Asunto:</strong> {{ $novedad->affair ?? 'Sin asunto' }}</span>
                                    <span class="text-xs text-muted"><strong>{{ $novedad->time?->format('H:i') ?? '--:--' }} hs</strong></span>
                                </div>
                            </li>
                        @empty
                            <li class="item text-center text-muted py-3">
                                <i class="fas fa-check-circle text-success mr-1"></i> Sin expedidos.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @else
            <div class="text-center py-3 text-muted">
                No hay guardia activa para mostrar noticias.
            </div>
        @endif
    </div>
</div>
