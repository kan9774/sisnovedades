<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <input type="text" wire:model.live.debounce.400ms="busqueda"
                           class="form-control" placeholder="Buscar por nombre...">
                </div>
                <div class="col-md-4 text-right">
                    @can('create', \App\Models\Ubicacion::class)
                        <button wire:click="abrirModalCrear" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva ubicación
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ubicaciones as $ubicacion)
                        <tr wire:key="ubicacion-{{ $ubicacion->id }}">
                            <td>{{ $ubicacion->nombre }}</td>
                            <td>{{ $ubicacion->descripcion ?? '—' }}</td>
                            <td class="text-right">
                                @can('update', $ubicacion)
                                    <button wire:click="abrirModalEditar({{ $ubicacion->id }})"
                                            class="btn btn-sm btn-outline-secondary" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                @endcan
                                @can('delete', $ubicacion)
                                    <button wire:click="eliminar({{ $ubicacion->id }})"
                                            wire:confirm="¿Eliminar esta ubicación?"
                                            class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                No hay ubicaciones que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $ubicaciones->links() }}
        </div>
    </div>

    {{-- Panel de alta/edición estilo ops --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalUbicacion" wire:ignore.self>
        <div class="ops-panel">
            <form wire:submit="guardar" class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Inventario</span>
                        <h5 class="ops-panel__title">
                            {{ $ubicacionId ? 'Editar ubicación' : 'Nueva ubicación' }}
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" onclick="cerrarOpsPanel('modalUbicacion')" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror">
                            @error('nombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Descripción (opcional)</label>
                            <textarea wire:model="descripcion" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarOpsPanel('modalUbicacion')">Cancelar</button>
                    <button type="submit" class="btn btn-ops-primary" wire:loading.attr="disabled" wire:target="guardar">
                        <span wire:loading.remove wire:target="guardar"><i class="fas fa-save"></i> Guardar</span>
                        <span wire:loading wire:target="guardar"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>
</div>


@script
    <script>
        if (!window.cerrarOpsPanel) {
            window.cerrarOpsPanel = function (id) {
                const overlay = document.getElementById(id);
                if (overlay) overlay.classList.remove('is-open');
                document.body.classList.remove('ops-panel-open');
            };
        }

        $wire.on('abrir-modal-ubicacion', () => {
            document.getElementById('modalUbicacion').classList.add('is-open');
            document.body.classList.add('ops-panel-open');
        });

        $wire.on('cerrar-modal-ubicacion', () => {
            cerrarOpsPanel('modalUbicacion');
        });
    </script>
@endscript