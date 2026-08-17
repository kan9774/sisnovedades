<div>
    <x-ops-card title="Organismos" icon="building" eyebrow="{{ $organismos->total() }} registros">
        <x-slot name="actions">
            @can('create', \App\Models\Organismo::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                    Nuevo Organismo
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
                            <i class="fas fa-plus-circle text-primary"></i> Nuevo Organismo
                        @else
                            <i class="fas fa-edit text-warning"></i> Editar Organismo
                        @endif
                    </h6>

                    <form wire:submit="guardar">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Nombre</label>
                                <input type="text" wire:model="formNombre"
                                    class="form-control @error('formNombre') is-invalid @enderror"
                                    placeholder="Nombre del organismo">
                                @error('formNombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 text-end">
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
                        <th style="width: 60%">Nombre</th>
                        <th style="width: 20%">Novedades</th>
                        <th style="width: 20%" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($organismos as $organismo)
                        <tr wire:key="organismo-{{ $organismo->id }}">
                            <td>{{ $organismo->name }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $organismo->novedades_count }}</span>
                            </td>
                            <td class="text-end">
                                @can('update', $organismo)
                                    <x-btn-ops variant="warning" icon="pen"
                                        wire:click="abrirEditar({{ $organismo->id }})"
                                        size="xs" title="Editar">
                                    </x-btn-ops>
                                @endcan
                                @can('delete', $organismo)
                                    <x-btn-ops variant="danger" icon="trash"
                                        wire:click="eliminar({{ $organismo->id }})"
                                        wire:confirm="¿Eliminar este organismo?"
                                        size="xs" title="Eliminar">
                                    </x-btn-ops>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                No hay organismos cargados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        @if ($organismos->hasPages())
            <div class="card-footer bg-white border-0 pt-3">
                {{ $organismos->links() }}
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
