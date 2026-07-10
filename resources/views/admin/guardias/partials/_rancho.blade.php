<form action="{{ route('admin.guardias.rancho.update', $guardia) }}" method="POST">
    @csrf
    @method('PUT')

    @php $bloqueado = $guardia->status !== 'open' || !$puedeOperarGuardia; @endphp

    <div class="form-row mb-3">
        @foreach (['desayuno' => 'Desayuno', 'almuerzo' => 'Almuerzo', 'merienda' => 'Merienda', 'cena' => 'Cena'] as $key => $label)
            <div class="col-md-3">
                <label class="small mb-1">Menú {{ $label }}</label>
                <input type="text" name="menu_{{ $key }}"
                    value="{{ old('menu_' . $key, $guardia->ranchoMenu->{'menu_' . $key} ?? '') }}"
                    class="form-control form-control-sm" placeholder="Agregue el menú"
                    {{ $bloqueado ? 'disabled' : '' }}>
            </div>
        @endforeach
    </div>

    <table class="table table-sm table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Unidad</th>
                <th style="width:100px;">Desayuno</th>
                <th style="width:100px;">Almuerzo</th>
                <th style="width:100px;">Merienda</th>
                <th style="width:100px;">Cena</th>
                <th style="width:70px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($unidadesActivas as $unidad)
                @php $registro = $rancho->get($unidad->id); @endphp
                <tr>
                    <td class="align-middle"><strong>{{ $unidad->nombre }}</strong></td>
                    @foreach (['desayuno', 'almuerzo', 'merienda', 'cena'] as $comida)
                        <td>
                            <input type="number" min="0" name="unidades[{{ $unidad->id }}][{{ $comida }}]"
                                value="{{ old("unidades.$unidad->id.$comida", $registro->{$comida} ?? '') }}"
                                class="form-control form-control-sm" {{ $bloqueado ? 'disabled' : '' }}>
                        </td>
                    @endforeach
                    <td class="align-middle text-center font-weight-bold">{{ $registro->total ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @unless ($bloqueado)
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-save"></i> Guardar Novedades de Rancho
        </button>
    @endunless
</form>