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
                           class="form-control" placeholder="Buscar por valor o sistema...">
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- FILA DE ALTA --}}
            @can('create', \App\Models\Talla::class)
                <form wire:submit="agregar">
                    <div class="row align-items-start mb-3">
                        <div class="col-md-4">
                            <label class="font-weight-bold">Valor</label>
                            <input type="text" wire:model="valor"
                                   class="form-control @error('valor') is-invalid @enderror"
                                   placeholder='Ej: "M", "42", "XL"'>
                            @error('valor') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="font-weight-bold">Sistema</label>
                            <input type="text" wire:model="sistema"
                                   class="form-control @error('sistema') is-invalid @enderror"
                                   placeholder='Ej: "Nacional", "EU", "US"'>
                            @error('sistema') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="font-weight-bold">Orden</label>
                            <input type="number" wire:model="orden" min="0"
                                   class="form-control @error('orden') is-invalid @enderror">
                            @error('orden') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
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
                    <thead class="thead-dark">
                        <tr>
                            <th>Valor</th>
                            <th>Sistema</th>
                            <th style="width: 10%">Orden</th>
                            <th style="width: 10%" class="text-center">Ítems</th>
                            <th style="width: 12%" class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tallas as $talla)
                            <tr wire:key="talla-{{ $talla->id }}">
                                @if ($editingId === $talla->id)
                                    {{-- FILA EN MODO EDICIÓN --}}
                                    <td>
                                        <input type="text" wire:model="editValor"
                                               class="form-control form-control-sm @error('editValor') is-invalid @enderror">
                                        @error('editValor') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="text" wire:model="editSistema"
                                               class="form-control form-control-sm @error('editSistema') is-invalid @enderror">
                                        @error('editSistema') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" wire:model="editOrden" min="0"
                                               class="form-control form-control-sm @error('editOrden') is-invalid @enderror">
                                        @error('editOrden') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $talla->items_count }}</span>
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
                                    <td><strong>{{ $talla->valor }}</strong></td>
                                    <td>{{ $talla->sistema ?? '—' }}</td>
                                    <td>{{ $talla->orden ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $talla->items_count }}</span>
                                    </td>
                                    <td class="text-right">
                                        @can('update', $talla)
                                            <button wire:click="startEdit({{ $talla->id }})"
                                                    class="btn btn-outline-secondary btn-sm" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        @endcan
                                        @can('delete', $talla)
                                            <button wire:click="eliminar({{ $talla->id }})"
                                                    wire:confirm="¿Eliminar esta talla?"
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
                                    No hay talles que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $tallas->links() }}
        </div>
    </div>
</div>