<div>
    <button type="button" wire:click="abrir" class="btn btn-primary btn-sm" title="Gestionar {{ $this->titulo() }}" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3); border: none;">
        <i class="fas fa-cog"></i>
    </button>

    @if ($abierto)
        <div class="modal d-block" style="background: rgba(255, 255, 255, 0.15) !important; backdrop-filter: blur(12px) saturate(180%) !important; -webkit-backdrop-filter: blur(12px) saturate(180%) !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; border-radius: 16px !important; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;" wire:click.self="cerrar">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
                    <div class="modal-header" style="background-color: #eef2ff !important;">
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
                                <button type="submit" class="btn btn-primary btn-sm btn-block" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3) !important;">
                                    <i class="fas fa-save"></i>
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