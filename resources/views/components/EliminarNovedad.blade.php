<div class="d-inline">
    @can('delete', $novedad)
        <button type="button" wire:click="eliminar" wire:confirm="¿Eliminar esta novedad?"
            wire:loading.attr="disabled" wire:target="eliminar"
            class="btn btn-outline-danger btn-sm" style="background-color: rgba(220, 53, 69, 0.08);"
            aria-label="Eliminar novedad">
            <span wire:loading.remove wire:target="eliminar">
                <i class="fas fa-trash"></i> Eliminar
            </span>
            <span wire:loading wire:target="eliminar">
                <i class="fas fa-spinner fa-spin"></i> Eliminando...
            </span>
        </button>
    @endcan
</div>