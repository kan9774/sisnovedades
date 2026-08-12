<div>

    {{-- ALERTAS --}}
    @if ($successMsg)
        <div wire:key="success-{{ md5($successMsg) }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.set('successMsg', '')
        }, 4000)"
            x-transition class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ $successMsg }}
            <button type="button" class="close" wire:click="$set('successMsg', '')">&times;</button>
        </div>
    @endif

    @if ($errorMsg)
        <div wire:key="error-{{ md5($errorMsg) }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.set('errorMsg', '')
        }, 5000)"
            x-transition class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ $errorMsg }}
            <button type="button" class="close" wire:click="$set('errorMsg', '')">&times;</button>
        </div>
    @endif

    <div class="d-flex gap-1">
        @can('cerrar', $guardia)
            <button type="button" class="btn-ops btn-ops-secondary btn-ops-icon" data-toggle="tooltip"
                title="Cerrar guardia" wire:click="cerrar" wire:loading.attr="disabled">
                <i class="fas fa-lock text-danger"></i>
            </button>
        @endcan
        @can('reactivar', $guardia)
            <button type="button" class="btn-ops btn-ops-secondary btn-ops-icon" data-toggle="tooltip"
                title="Reactivar guardia"
                x-on:click="
                    confirmarAccion({
                        title: '¿Reactivar guardia?',
                        text: '¿Está seguro que desea reactivar esta guardia? Volverá a estado Abierta.',
                        icon: 'warning',
                        confirmButtonText: 'Sí, reactivar',
                        confirmButtonColor: '#ffc107',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $wire.ejecutarReactivar();
                        }
                    });
                "
                wire:loading.attr="disabled"
                wire:target="ejecutarReactivar">
                <i class="fas fa-lock-open text-warning"></i>
            </button>
        @endcan
    </div>

    <script>
        document.addEventListener('livewire:load', () => {
            $wire.on('mostrarAlertaCerrar', (count) => {
                confirmarAccion({
                    title: 'Cerrar con novedades pendientes',
                    text: 'No se puede cerrar: quedan ' + count + ' novedad(es) sin resolver (Caso a resolver).',
                    icon: 'warning',
                    confirmButtonText: 'Cerrar de todas formas',
                    confirmButtonColor: '#dc3545',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.cerrarForzado();
                    }
                });
            });
        });
    </script>

</div>
