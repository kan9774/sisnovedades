<div>
    <x-ops-card title="Estados de Palomas" icon="tags" eyebrow="{{ $estadosPaloma->total() }} registros">
        <x-slot name="actions">
            @can('create', \App\Models\EstadoPaloma::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                    Nuevo Estado
                </x-btn-ops>
            @endcan
        </x-slot>

        {{-- BARRA DE BÚSQUEDA --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Buscar por nombre...">
            </div>
        </div>

        {{-- FORMULARIO INLINE (create / edit) --}}
        @if ($showForm)
            <div class="card mb-3" style="background-color: #f8f9fa;">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        @if ($formTipo === 'create')
                            <i class="fas fa-plus-circle text-primary"></i> Nuevo Estado
                        @else
                            <i class="fas fa-edit text-warning"></i> Editar Estado
                        @endif
                    </h6>

                    <form wire:submit="guardar">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nombre</label>
                                <input type="text" wire:model="formNombre"
                                    class="form-control @error('formNombre') is-invalid @enderror"
                                    placeholder="Ej: Activa, En recuperación">
                                @error('formNombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Color</label>
                                <input type="text" wire:model="formColor"
                                    class="form-control @error('formColor') is-invalid @enderror"
                                    placeholder="Ej: #ff5733 (opcional)">
                                @error('formColor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <div class="form-check form-switch mt-4">
                                    <input type="checkbox" wire:model="formActivo" class="form-check-input"
                                        id="formActivo">
                                    <label class="form-check-label" for="formActivo">Activo</label>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <button type="submit" class="btn btn-primary btn-sm me-2"
                                    wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                                    @if ($loading)
                                        <span class="spinner-border spinner-border-sm"></span>
                                    @endif
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <x-btn-ops variant="secondary" icon="times" wire:click="cerrarForm" size="sm">
                                    Cancelar
                                </x-btn-ops>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- TABLA --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th style="width: 30%">Nombre</th>
                        <th style="width: 20%">Color</th>
                        <th style="width: 12%">Estado</th>
                        <th style="width: 13%">Palomas</th>
                        <th style="width: 25%" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estadosPaloma as $estado)
                        <tr wire:key="estado-{{ $estado->id }}">
                            <td><strong>{{ $estado->nombre }}</strong></td>
                            <td>
                                @if ($estado->color)
                                    <span class="badge" style="background-color: {{ $estado->color }}; color: #fff; padding: 5px 12px; border-radius: 50px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;">
                                        <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: {{ $estado->color }}; border: 1px solid rgba(255,255,255,0.3);"></span>
                                        {{ $estado->color }}
                                    </span>
                                @else
                                    <span class="text-muted">Sin color</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($estado->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $estado->palomas_count }}</span>
                            </td>
                            <td class="text-end">
                                @can('update', $estado)
                                    <x-btn-ops variant="warning" icon="pen"
                                        wire:click="abrirEditar({{ $estado->id }})"
                                        size="xs" title="Editar">
                                    </x-btn-ops>
                                @endcan
                                @can('delete', $estado)
                                    <x-btn-ops variant="danger" icon="trash"
                                        wire:click="eliminar({{ $estado->id }})"
                                        wire:confirm="¿Eliminar este estado?"
                                        size="xs" title="Eliminar">
                                    </x-btn-ops>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay estados registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($estadosPaloma->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $estadosPaloma->links() }}
            </div>
        @endif
    </x-ops-card>

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
