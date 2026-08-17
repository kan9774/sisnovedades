<div>
    <x-ops-card title="Oficinas" icon="building" eyebrow="{{ $oficinas->total() }} registros">
        <x-slot name="actions">
            @can('create', \App\Models\Oficina::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                    Nueva Oficina
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
                            <i class="fas fa-plus-circle text-primary"></i> Nueva Oficina
                        @else
                            <i class="fas fa-edit text-warning"></i> Editar Oficina
                        @endif
                    </h6>

                    <form wire:submit="guardar">
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nombre</label>
                                <input type="text" wire:model="formNombre"
                                    class="form-control @error('formNombre') is-invalid @enderror"
                                    placeholder="Nombre de la oficina">
                                @error('formNombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input type="checkbox" wire:model="formActivo" class="form-check-input"
                                        id="formActivo">
                                    <label class="form-check-label" for="formActivo">Activa</label>
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
                        <th style="width: 40%">Nombre</th>
                        <th style="width: 15%">Usuarios</th>
                        <th style="width: 12%">Estado</th>
                        <th style="width: 33%" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($oficinas as $oficina)
                        <tr wire:key="oficina-{{ $oficina->id }}">
                            <td>{{ $oficina->nombre }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $oficina->users_count }}</span>
                            </td>
                            <td class="text-center">
                                @if ($oficina->activo)
                                    <span class="badge bg-success">Activa</span>
                                @else
                                    <span class="badge bg-secondary">Inactiva</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('update', $oficina)
                                    <x-btn-ops variant="warning" icon="pen"
                                        wire:click="abrirEditar({{ $oficina->id }})"
                                        size="xs" title="Editar">
                                    </x-btn-ops>
                                @endcan
                                @can('delete', $oficina)
                                    <x-btn-ops variant="danger" icon="trash"
                                        wire:click="eliminar({{ $oficina->id }})"
                                        wire:confirm="¿Eliminar esta oficina?"
                                        size="xs" title="Eliminar">
                                    </x-btn-ops>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No hay oficinas cargadas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($oficinas->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $oficinas->links() }}
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
