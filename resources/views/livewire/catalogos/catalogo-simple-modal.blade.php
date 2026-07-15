<div>
    <button type="button" wire:click="abrir" class="btn btn-outline-secondary btn-sm" title="Gestionar {{ $this->titulo() }}">
        <i class="fas fa-cog"></i>
    </button>

    @if ($abierto)
        <div class="modal d-block" style="background: rgba(0,0,0,.5)" wire:click.self="cerrar">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $this->titulo() }}</h5>
                        <button type="button" class="close" wire:click="cerrar"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="guardar" class="row align-items-end mb-3">
                            <div class="col-7">
                                <label class="mb-1">Nombre</label>
                                <input type="text" wire:model="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror">
                                @error('nombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="activo-{{ $this->getId() }}" wire:model="activo">
                                    <label class="custom-control-label" for="activo-{{ $this->getId() }}">Activo</label>
                                </div>
                            </div>
                            <div class="col-2">
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    {{ $editandoId ? 'Guardar' : 'Agregar' }}
                                </button>
                            </div>
                        </form>

                        <table class="table table-sm">
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $item['nombre'] }}</td>
                                        <td>
                                            @if ($item['activo'])
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-outline-warning btn-xs" wire:click="editar({{ $item['id'] }})"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-outline-danger btn-xs" wire:click="eliminar({{ $item['id'] }})" onclick="return confirm('¿Eliminar?')"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>