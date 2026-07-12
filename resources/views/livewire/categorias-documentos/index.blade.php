<div>
    {{-- ALERTAS GLOBALES --}}
    @if ($successMsg)
        <div wire:key="success-{{ md5($successMsg) }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.set('successMsg', '')
        }, 4000)"
            x-transition class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ $successMsg }}
            <button type="button" class="close" wire:click="$set('successMsg', '')">&times;</button>
        </div>
    @endif

    @if ($errorMsg)
        <div wire:key="error-{{ md5($errorMsg) }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.set('errorMsg', '')
        }, 5000)"
            x-transition class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ $errorMsg }}
            <button type="button" class="close" wire:click="$set('errorMsg', '')">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-folder"></i> Categorías</h3>
            <div class="card-tools">
                <a href="{{ route('admin.documentos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver a documentos
                </a>
            </div>
        </div>

        <div class="card-body">
            {{-- FILA DE ALTA --}}
            @can('create', App\Models\CategoriaDocumento::class)
                <form wire:submit="agregar">
                    <div class="row align-items-start mb-3">
                        <div class="col-md-4">
                            <label class="font-weight-bold">Nombre</label>
                            <input type="text" wire:model="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                placeholder="Ej: Normativas, Circulares...">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="font-weight-bold">Descripción</label>
                            <input type="text" wire:model="descripcion" class="form-control"
                                placeholder="Descripción opcional...">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled"
                                wire:target="agregar" @disabled($loading)>
                                @if ($loading)
                                    <span class="spinner-border spinner-border-sm"></span>
                                @else
                                    <i class="fas fa-plus"></i> Agregar
                                @endif
                            </button>
                        </div>
                    </div>
                </form>
            @endcan

            {{-- TABLA --}}
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th style="width: 20%">Nombre</th>
                            <th>Descripción</th>
                            <th style="width: 8%" class="text-center">Docs.</th>
                            <th style="width: 10%" class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categorias as $categoria)
                            <tr wire:key="categoria-{{ $categoria->id }}">
                                @if ($editingId === $categoria->id)
                                    {{-- FILA EN MODO EDICIÓN --}}
                                    <td>
                                        <input type="text" wire:model="editNombre"
                                            class="form-control form-control-sm @error('editNombre') is-invalid @enderror">
                                        @error('editNombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="text" wire:model="editDescripcion"
                                            class="form-control form-control-sm">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $categoria->documentos_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        <button wire:click="saveEdit" class="btn btn-success btn-sm"
                                            title="Guardar" wire:loading.attr="disabled" wire:target="saveEdit"
                                            @disabled($loading)>
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button wire:click="cancelEdit" class="btn btn-outline-secondary btn-sm"
                                            title="Cancelar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                @else
                                    {{-- FILA NORMAL --}}
                                    <td>{{ $categoria->nombre }}</td>
                                    <td class="text-muted">{{ $categoria->descripcion ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $categoria->documentos_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        @can('update', $categoria)
                                            <button wire:click="startEdit({{ $categoria->id }})"
                                                class="btn btn-outline-warning btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endcan
                                        @can('delete', $categoria)
                                            <button wire:click="eliminar({{ $categoria->id }})"
                                                wire:confirm="¿Eliminar esta categoría?"
                                                class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No hay categorías cargadas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>