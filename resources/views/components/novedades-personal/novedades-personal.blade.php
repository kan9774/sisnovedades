<div>
    @if ($guardia->status === 'open' && $puedeOperarGuardia)
        <form wire:submit="agregar" class="mb-3">
            <div class="form-row align-items-end">
                <div class="col-md-2">
                    <label class="small mb-1">Hora</label>
                    <input type="time" wire:model="hora" class="form-control form-control-sm @error('hora') is-invalid @enderror">
                </div>
                <div class="col-md-3">
                    <label class="small mb-1">Tipo</label>
                    <input type="text" wire:model="tipo" class="form-control form-control-sm @error('tipo') is-invalid @enderror"
                        placeholder="Diana, Rancho, Retreta...">
                </div>
                <div class="col-md-5">
                    <label class="small mb-1">Detalle</label>
                    <input type="text" wire:model="texto" class="form-control form-control-sm @error('texto') is-invalid @enderror">
                </div>
                <div class="col-md-2">
                    <x-btn-ops type="submit" icon="plus" variant="primary" size="sm" title="Agregar Novedad" wire:loading.attr="disabled" wire:target="agregar">
                        <span wire:loading.remove wire:target="agregar"> Agregar</span>
                        <span wire:loading wire:target="agregar"><i class="fas fa-spinner fa-spin"></i></span>
                    </x-btn-ops>
                </div>
            </div>
            @error('hora') <small class="text-danger d-block">{{ $message }}</small> @enderror
            @error('tipo') <small class="text-danger d-block">{{ $message }}</small> @enderror
            @error('texto') <small class="text-danger d-block">{{ $message }}</small> @enderror
        </form>
    @endif

    <table class="table table-sm table-striped">
        <thead class="thead-ops">
            <tr>
                <th style="width: 80px;">Hora</th>
                <th style="width: 160px;">Tipo</th>
                <th>Detalle</th>
                @if ($guardia->status === 'open' && $puedeOperarGuardia)
                    <th class="text-center" style="width: 90px;">-</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($this->novedades as $item)
                @if ($guardia->status === 'open' && $puedeOperarGuardia && $editingId === $item->id)
                    {{-- Fila en modo edición inline --}}
                    <tr wire:key="novedad-personal-edit-{{ $item->id }}">
                        <td>
                            <input type="time" wire:model="editHora"
                                class="form-control form-control-sm @error('editHora') is-invalid @enderror">
                            @error('editHora') <small class="text-danger d-block">{{ $message }}</small> @enderror
                        </td>
                        <td>
                            <input type="text" wire:model="editTipo"
                                class="form-control form-control-sm @error('editTipo') is-invalid @enderror">
                            @error('editTipo') <small class="text-danger d-block">{{ $message }}</small> @enderror
                        </td>
                        <td>
                            <input type="text" wire:model="editTexto"
                                class="form-control form-control-sm @error('editTexto') is-invalid @enderror">
                            @error('editTexto') <small class="text-danger d-block">{{ $message }}</small> @enderror
                        </td>
                        <td class="text-center text-nowrap">
                            <x-btn-ops wire:click="guardarEdicion" icon="check" variant="success" size="xs" title="Guardar"
                                wire:loading.attr="disabled" wire:target="guardarEdicion">
                            </x-btn-ops>
                            <x-btn-ops wire:click="cancelarEdicion" icon="times" variant="secondary" size="xs" title="Cancelar">
                            </x-btn-ops>
                        </td>
                    </tr>
                @else
                    {{-- Fila normal --}}
                    <tr wire:key="novedad-personal-{{ $item->id }}">
                        <td>{{ $item->hora->format('H:i') }}</td>
                        <td>{{ $item->tipo }}</td>
                        <td>{{ $item->texto }}</td>
                        @if ($guardia->status === 'open' && $puedeOperarGuardia)
                            <td class="text-center text-nowrap">
                                <x-btn-ops wire:click="editar({{ $item->id }})" icon="pen" variant="warning" size="xs" title="Editar">
                                </x-btn-ops>
                                <x-btn-ops wire:click="eliminar({{ $item->id }})" wire:confirm="¿Eliminar este registro?" icon="trash" variant="danger" size="xs" title="Eliminar">
                                </x-btn-ops>
                            </td>
                        @endif
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">Sin novedades de personal registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($this->novedades->hasPages())
        <div class="mt-3">{{ $this->novedades->links() }}</div>
    @endif
</div>