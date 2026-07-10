@if ($guardia->status === 'open' && $puedeOperarGuardia)
    <form action="{{ route('admin.guardias.personal.store', $guardia) }}" method="POST" class="mb-3">
        @csrf
        <div class="form-row align-items-end">
            <div class="col-md-2">
                <label class="small mb-1">Hora</label>
                <input type="time" name="hora" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <label class="small mb-1">Tipo</label>
                <input type="text" name="tipo" class="form-control form-control-sm"
                    placeholder="Diana, Rancho, Retreta..." required>
            </div>
            <div class="col-md-5">
                <label class="small mb-1">Detalle</label>
                <input type="text" name="texto" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-plus"></i> Agregar
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
        @forelse ($novedadesPersonal as $item)
            <tr>
                <td>{{ $item->hora }}</td>
                <td>{{ $item->tipo }}</td>
                <td>{{ $item->texto }}</td>
                @if ($guardia->status === 'open' && $puedeOperarGuardia)
                    <td class="text-center">
                        <form action="{{ route('admin.guardias.personal.destroy', [$guardia, $item]) }}"
                            method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este registro?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-xs"><i class="fas fa-trash"></i></button>
                        </form>
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