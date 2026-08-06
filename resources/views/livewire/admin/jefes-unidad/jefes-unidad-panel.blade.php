<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible py-2">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <h6 class="mb-2">Jefe de Unidad</h6>
    <small class="text-muted d-block mb-2">
        Se usa para completar automáticamente la autoridad firmante de los contratos C.S.M.
    </small>

    {{-- Formulario inline: 5 campos en una fila --}}
    <div class="row align-items-end g-2 mb-2">
        <div class="col-md-3">
            <label class="small mb-1">Nombre completo</label>
            <input type="text" wire:model="nombre_completo"
                class="form-control form-control-sm @error('nombre_completo') is-invalid @enderror">
            @error('nombre_completo')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-md-2">
            <label class="small mb-1">Grado</label>
            <select wire:model="grado_id" class="form-control form-control-sm @error('grado_id') is-invalid @enderror">
                <option value="">Seleccionar...</option>
                @foreach ($grados as $grado)
                    <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                @endforeach
            </select>
            @error('grado_id')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>
        <div class="col-md-2">
            <label class="small mb-1">Cargo</label>
            <input type="text" wire:model="cargo" placeholder="Jefe de Unidad"
                class="form-control form-control-sm @error('cargo') is-invalid @enderror">
            @error('cargo')
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
                    <th>Nombre</th>
                    <th>Grado</th>
                    <th>Cargo</th>
                    <th>Desde</th>
                    <th>Hasta</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->jefes as $jefe)
                    <tr class="{{ $editandoId === $jefe->id ? 'table-warning' : '' }}">
                        <td>{{ $jefe->nombre_completo }}</td>
                        <td>{{ $jefe->grado->nombre }}</td>
                        <td>{{ $jefe->cargo }}</td>
                        <td>{{ $jefe->fecha_desde->format('d/m/Y') }}</td>
                        <td>
                            @if ($jefe->id === $this->vigenteId)
                                <span class="badge-ops badge-ops-success">Vigente</span>
                            @else
                                {{ $jefe->fecha_hasta?->format('d/m/Y') ?? '—' }}
                            @endif
                        </td>
                        <td>
                            <div class="ops-actions">
                                <button type="button" class="btn-ops btn-ops-icon btn-ops-icon--xs btn-ops-warning"
                                    wire:click="editar({{ $jefe->id }})" title="Editar">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button" class="btn-ops btn-ops-icon btn-ops-icon--xs btn-ops-danger"
                                    wire:click="eliminar({{ $jefe->id }})"
                                    wire:confirm="¿Eliminar este Jefe de Unidad?"
                                    title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            Todavía no hay Jefes de Unidad cargados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>