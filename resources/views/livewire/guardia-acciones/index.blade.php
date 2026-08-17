<div>
    <div class="d-flex gap-1">
        @if($guardia->status === 'open')
            @can('cerrar', $guardia)
                <button type="button" class="btn-ops btn-ops-secondary btn-ops-icon" data-toggle="tooltip"
                    title="Cerrar guardia" wire:click="cerrar" wire:loading.attr="disabled">
                    <i class="fas fa-lock text-danger"></i>
                </button>
            @endcan
        @endif
        @if($guardia->status === 'closed')
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
        @endif
    </div>

    @script
    <script>
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

        $wire.$watch('successMsg', (valor) => {
            mostrarToast('success', valor);
        });

        $wire.$watch('errorMsg', (valor) => {
            mostrarToast('error', valor);
        });
    </script>
    @endscript

</div>
