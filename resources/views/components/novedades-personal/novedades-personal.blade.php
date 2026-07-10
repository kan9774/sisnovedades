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
                    <button type="submit" class="btn btn-primary btn-sm btn-block"
                        wire:loading.attr="disabled" wire:target="agregar">
                        <span wire:loading.remove wire:target="agregar"><i class="fas fa-plus"></i> Agregar</span>
                        <span wire:loading wire:target="agregar"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </div>
            @error('hora') <small class="text-danger d-block">{{ $message }}</small> @enderror
            @error('tipo') <small class="text-danger d-block">{{ $message }}</small> @enderror
            @error('texto') <small class="text-danger d-block">{{ $message }}</small> @enderror
        </form>
    @endif

    <table class="table table-sm table-striped">
        <thead class="thead-dark">
            <tr>
                <th style="width: 80px;">Hora</th>
                <th style="width: 160px;">Tipo</th>
                <th>Detalle</th>
                @if ($guardia->status === 'open' && $puedeOperarGuardia)
                    <th class="text-center" style="width: 60px;">-</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($this->novedades as $item)
                <tr wire:key="novedad-personal-{{ $item->id }}">
                    <td>{{ $item->hora->format('H:i') }}</td>
                    <td>{{ $item->tipo }}</td>
                    <td>{{ $item->texto }}</td>
                    @if ($guardia->status === 'open' && $puedeOperarGuardia)
                        <td class="text-center">
                            <button wire:click="eliminar({{ $item->id }})" wire:confirm="¿Eliminar este registro?"
                                class="btn btn-outline-danger btn-xs">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">Sin novedades de personal registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>