<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible py-2">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <h6 class="mb-2">Credencial Cívica</h6>
    <small class="text-muted d-block mb-2">
        La vigente (sin fecha "hasta") completa automáticamente serie y número en el contrato.
    </small>

    {{-- Formulario inline: 5 campos en una fila --}}
    <div class="row align-items-end g-2 mb-2">
        <div class="col-md-3">
            <label class="small mb-1">Departamento</label>
            <select wire:model="departamento_id"
                class="form-control form-control-sm @error('departamento_id') is-invalid @enderror">
                <option value="">Seleccionar...</option>
                @foreach ($departamentos as $departamento)
                    <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                @endforeach
            </select>
            @error('departamento_id')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-md-2">
            <label class="small mb-1">Serie</label>
            <input type="text" wire:model="serie"
                class="form-control form-control-sm @error('serie') is-invalid @enderror">
            @error('serie')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-md-2">
            <label class="small mb-1">Número</label>
            <input type="text" wire:model="numero"
                class="form-control form-control-sm @error('numero') is-invalid @enderror">
            @error('numero')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-md-2">
            <label class="small mb-1">Desde</label>
            <input type="date" wire:model="fecha_desde"
                class="form-control form-control-sm @error('fecha_desde') is-invalid @enderror">
            @error('fecha_desde')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-md-2">
            <label class="small mb-1">Hasta <span class="text-muted">(opc.)</span></label>
            <input type="date" wire:model="fecha_hasta"
                class="form-control form-control-sm @error('fecha_hasta') is-invalid @enderror">
            @error('fecha_hasta')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button type="button" class="btn-ops btn-ops-icon btn-ops-icon--sm btn-ops-primary"
                wire:click="guardar" title="{{ $editandoId ? 'Guardar cambios' : 'Agregar' }}">
                <i class="fas {{ $editandoId ? 'fa-check' : 'fa-plus' }}"></i>
            </button>
            @if ($editandoId)
                <button type="button" class="btn btn-sm btn-outline-secondary"
                    wire:click="cancelarEdicion" title="Cancelar">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
    </div>

    {{-- Listado --}}
    <div class="table-responsive">
        <table class="table table-sm table-ops-hover mb-0">
            <thead class="thead-ops">
                <tr>
                    <th>Departamento</th>
                    <th>Serie</th>
                    <th>Número</th>
                    <th>Desde</th>
                    <th>Hasta</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->credenciales as $credencial)
                    <tr class="{{ $editandoId === $credencial->id ? 'table-warning' : '' }}">
                        <td>{{ $credencial->departamento->nombre }}</td>
                        <td>{{ $credencial->serie }}</td>
                        <td>{{ $credencial->numero }}</td>
                        <td>{{ $credencial->fecha_desde->format('d/m/Y') }}</td>
                        <td>
                            @if (is_null($credencial->fecha_hasta))
                                <span class="badge-ops badge-ops-success">Vigente</span>
                            @else
                                {{ $credencial->fecha_hasta->format('d/m/Y') }}
                            @endif
                        </td>
                        <td>
                            <div class="ops-actions">
                                <button type="button" class="btn-ops btn-ops-icon btn-ops-icon--xs btn-ops-warning"
                                    wire:click="editar({{ $credencial->id }})" title="Editar">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button" class="btn-ops btn-ops-icon btn-ops-icon--xs btn-ops-danger"
                                    wire:click="eliminar({{ $credencial->id }})"
                                    wire:confirm="¿Eliminar esta credencial cívica?"
                                    title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            Todavía no hay credenciales cívicas cargadas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>