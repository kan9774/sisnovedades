{{-- resources/views/livewire/inventario/grados-catalogo.blade.php --}}
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
                           class="form-control" placeholder="Buscar grado...">
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- FILA DE ALTA --}}
            @can('create', \App\Models\Grado::class)
                <form wire:submit="agregar">
                    <div class="row align-items-start mb-3">
                        <div class="col-md-6">
                            <label class="font-weight-bold">Nombre</label>
                            <input type="text" wire:model="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   placeholder="Ej: Capitán, Sgto.(EC)...">
                            @error('nombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="font-weight-bold">
                                Orden
                                <small class="text-muted font-weight-normal">(jerarquía, opcional)</small>
                            </label>
                            <input type="number" wire:model="orden" min="0"
                                   class="form-control @error('orden') is-invalid @enderror">
                            @error('orden') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="font-weight-bold d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block"
                                    wire:loading.attr="disabled" wire:target="agregar">
                                <span wire:loading.remove wire:target="agregar"><i class="fas fa-plus"></i> Agregar</span>
                                <span wire:loading wire:target="agregar"><i class="fas fa-spinner fa-spin"></i> Guardando...</span>
                            </button>
                        </div>
                    </div>
                </form>
            @endcan

            {{-- TABLA --}}
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-ops">
                        <tr>
                            <th style="width: 10%">Orden</th>
                            <th>Nombre</th>
                            <th style="width: 12%" class="text-center">Personal</th>
                            <th style="width: 12%" class="text-center">Estado</th>
                            <th style="width: 15%" class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($grados as $grado)
                            <tr wire:key="grado-{{ $grado->id }}">
                                @if ($editingId === $grado->id)
                                    {{-- FILA EN MODO EDICIÓN --}}
                                    <td>
                                        <input type="number" wire:model="editOrden" min="0"
                                               class="form-control form-control-sm @error('editOrden') is-invalid @enderror">
                                    </td>
                                    <td>
                                        <input type="text" wire:model="editNombre"
                                               class="form-control form-control-sm @error('editNombre') is-invalid @enderror">
                                        @error('editNombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $grado->usuarios_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($grado->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <button wire:click="saveEdit" class="btn btn-success btn-sm" title="Guardar"
                                                wire:loading.attr="disabled" wire:target="saveEdit">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button wire:click="cancelEdit" class="btn btn-outline-secondary btn-sm" title="Cancelar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                @else
                                    {{-- FILA NORMAL --}}
                                    <td>{{ $grado->orden ?? '—' }}</td>
                                    <td>{{ $grado->nombre }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $grado->usuarios_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @can('update', $grado)
                                            <button wire:click="toggleActivo({{ $grado->id }})"
                                                    class="btn btn-sm {{ $grado->activo ? 'btn-success' : 'btn-secondary' }}"
                                                    title="Click para {{ $grado->activo ? 'desactivar' : 'activar' }}">
                                                {{ $grado->activo ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        @else
                                            <span class="badge {{ $grado->activo ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $grado->activo ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        @endcan
                                    </td>
                                    <td class="text-right">
                                        @can('update', $grado)
                                            <button wire:click="startEdit({{ $grado->id }})"
                                                    class="btn btn-outline-secondary btn-sm" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        @endcan
                                        @can('delete', $grado)
                                            <button wire:click="eliminar({{ $grado->id }})"
                                                    wire:confirm="¿Eliminar este grado?"
                                                    class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay grados que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $grados->links() }}
        </div>
    </div>
</div>