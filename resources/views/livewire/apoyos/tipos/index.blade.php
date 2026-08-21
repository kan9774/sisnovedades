<div>
    <x-ops-card title="Tipos de Apoyo" icon="tags" eyebrow="{{ $tiposApoyo->total() }} registros">
        <x-slot name="actions">
            @can('create', \App\Models\TipoApoyo::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                    Nuevo Tipo
                </x-btn-ops>
            @endcan
        </x-slot>

        {{-- BARRA DE BÚSQUEDA --}}
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="form-control border-left-0"
                        placeholder="Buscar por nombre...">
                </div>
            </div>
            <div class="col-md-4 text-right">
                @if ($search)
                    <button wire:click="clearFilters" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-times"></i> Limpiar filtros
                    </button>
                @endif
            </div>
        </div>

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th style="width: 8%"></th>
                        <th style="width: 40%">Nombre</th>
                        <th style="width: 20%" class="text-center">Color</th>
                        <th style="width: 15%" class="text-center">Apoyos</th>
                        <th style="width: 17%" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tiposApoyo as $tipo)
                        <tr wire:key="tipo-apoyo-{{ $tipo->id }}">
                            <td class="text-center">
                                <span class="d-inline-block rounded-circle"
                                      style="width: 24px; height: 24px; background-color: {{ $tipo->color }}; border: 2px solid #dee2e6;">
                                </span>
                            </td>
                            <td>
                                <strong>{{ $tipo->nombre }}</strong>
                            </td>
                            <td class="text-center">
                                <code>{{ $tipo->color }}</code>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $tipo->apoyos_count }}</span>
                            </td>
                            <td class="text-end">
                                @can('update', $tipo)
                                    <x-btn-ops variant="warning" icon="pen"
                                        wire:click="abrirEditar({{ $tipo->id }})"
                                        size="xs" title="Editar">
                                    </x-btn-ops>
                                @endcan
                                @can('delete', $tipo)
                                    <x-btn-ops variant="danger" icon="trash"
                                        wire:click="confirmDelete({{ $tipo->id }})"
                                        size="xs" title="Eliminar">
                                    </x-btn-ops>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay tipos de apoyo cargados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($tiposApoyo->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $tiposApoyo->links() }}
            </div>
        @endif
    </x-ops-card>

    {{-- MODAL: FORMULARIO CREAR / EDITAR (ops-panel overlay) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalTiposApoyo"
         x-data
         x-init="$watch('$wire.showForm', value => {
             if (value) document.body.classList.add('ops-panel-open');
             else document.body.classList.remove('ops-panel-open');
         })"
         :class="{ 'is-open': $wire.showForm }"
         wire:click.self="cerrarForm">
        <div class="ops-panel">
            <div class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Administración</span>
                        <h5 class="ops-panel__title">
                            {{ $formTipo === 'create' ? 'Nuevo Tipo de Apoyo' : 'Editar Tipo de Apoyo' }}
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" wire:click="cerrarForm"
                        title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body" wire:loading.class="opacity-50" wire:target="guardar">
                    <div class="ops-panel__content">
                        @if ($justSaved)
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h5 class="text-success">{{ $successMsg }}</h5>
                            </div>
                        @else
                            @if ($errorMsg)
                                <div class="alert alert-danger">{{ $errorMsg }}</div>
                            @endif
                            <form wire:submit="guardar" id="form-tipo-apoyo">
                                <div class="form-group">
                                    <label>Nombre <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live="formNombre"
                                        class="form-control @error('formNombre') is-invalid @enderror"
                                        placeholder="Ej: Vehículos">
                                    @error('formNombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Color <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" wire:model.live="formColor"
                                            class="form-control form-control-color @error('formColor') is-invalid @enderror"
                                            style="width: 60px; height: 45px; padding: 4px; cursor: pointer;">
                                        <div>
                                            <code class="text-muted">{{ $formColor }}</code>
                                        </div>
                                    </div>
                                    @error('formColor')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="ops-panel__footer">
                    @if (!$justSaved)
                        <button type="button" class="btn btn-outline-secondary"
                            wire:click="cerrarForm"
                            wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                            Cancelar
                        </button>
                        <button type="submit" form="form-tipo-apoyo" class="btn btn-ops-primary"
                            wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                            <i class="fas fa-save"></i>
                            {{ $formTipo === 'create' ? 'Crear' : 'Guardar' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- MODAL: CONFIRMAR ELIMINACIÓN --}}
    @if ($confirmDeleteId)
        <div class="modal fade show d-block" tabindex="-1"
            style="background: rgba(255, 255, 255, 0.15) !important; backdrop-filter: blur(12px) saturate(180%) !important; -webkit-backdrop-filter: blur(12px) saturate(180%) !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; border-radius: 16px !important; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;"
            wire:click.self="$set('confirmDeleteId', null)" wire:keydown.escape="$set('confirmDeleteId', null)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar eliminación</h5>
                        <button type="button" class="close text-white"
                            wire:click="$set('confirmDeleteId', null)">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar este tipo de apoyo?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('confirmDeleteId', null)">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="executeDelete"
                            @disabled($loading)>
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span> Eliminando...
                            @else
                                <i class="fas fa-trash"></i> Eliminar
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
    <script>
        $wire.$watch('successMsg', (valor) => {
            mostrarToast('success', valor);
        });

        $wire.$watch('errorMsg', (valor) => {
            mostrarToast('error', valor);
        });
    </script>
    @endscript
</div>
