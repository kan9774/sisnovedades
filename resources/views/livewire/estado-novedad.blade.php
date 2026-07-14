<div @if (in_array($novedad->estado_atencion, ['pendiente', 'visto'])) wire:poll.5s="refrescar" @endif>
    @if ($novedad->estado_atencion === 'pendiente')
        <span class="badge badge-warning">
            <i class="fas fa-clock"></i> Pendiente
        </span>

        @can('tomar', $novedad)
            <button wire:click="tomar" wire:loading.attr="disabled" wire:target="tomar"
                class="btn btn-warning btn-xs {{ $compacto ? 'ml-1' : 'mt-1 d-block' }}">
                <span wire:loading.remove wire:target="tomar">
                    <i class="fas fa-hand-paper"></i> Tomar
                </span>
                <span wire:loading wire:target="tomar">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </button>
        @endcan
    @elseif ($novedad->estado_atencion === 'visto')
        <span class="badge badge-success">
            <i class="fas fa-check"></i> Visto
        </span>
        <br>
        @unless ($compacto)
        @endunless
        <small class="text-muted {{ $compacto ? 'd-block' : '' }}">
            {{ $novedad->tomadoPor->name ?? '—' }}
            {{ $compacto ? '' : 'el ' . optional($novedad->tomado_en)->format('d/m H:i') }}
        </small>
    @else
        <span class="text-muted small">—</span>
    @endif
</div>
