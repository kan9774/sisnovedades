<div x-data="{ editando: {{ session('success') ? 'false' : 'true' }} }">
    <form action="{{ route('admin.guardias.rancho.update', $guardia) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-row mb-3">
            @foreach (['desayuno' => 'Desayuno', 'colacion' => 'Colación', 'almuerzo' => 'Almuerzo', 'merienda' => 'Merienda', 'cena' => 'Cena'] as $key => $label)
                <div class="col">
                    <label class="small mb-1">Menú {{ $label }}</label>
                    <input type="text" name="menu_{{ $key }}"
                        value="{{ old('menu_' . $key, $guardia->ranchoMenu->{'menu_' . $key} ?? '') }}"
                        class="form-control form-control-sm" placeholder="Agregue el menú"
                        :disabled="!editando">
                </div>
            @endforeach
        </div>

        <table class="table table-sm table-bordered">
            <thead class="thead-ops">
                <tr>
                    <th>Unidad</th>
                    <th style="width:100px;">Desayuno</th>
                    <th style="width:100px;">Colación</th>
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
                        @foreach (['desayuno', 'colacion', 'almuerzo', 'merienda', 'cena'] as $comida)
                            <td>
                                <input type="number" min="0" name="unidades[{{ $unidad->id }}][{{ $comida }}]"
                                    value="{{ old("unidades.$unidad->id.$comida", $registro->{$comida} ?? '') }}"
                                    class="form-control form-control-sm" :disabled="!editando">
                            </td>
                        @endforeach
                        <td class="align-middle text-center font-weight-bold">{{ $registro->total ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Botón Editar -->
        <x-btn-ops type="button" icon="pen" variant="warning" size="sm"
            x-show="!editando" @click="editando = true"
            title="Habilitar edición">
            Editar
        </x-btn-ops>

        <!-- Botón Guardar -->
        <x-btn-ops type="submit" icon="save" variant="info" size="sm"
            x-show="editando"
            title="Guardar Novedades de Rancho">
            Guardar Novedades de Rancho
        </x-btn-ops>
    </form>
</div>