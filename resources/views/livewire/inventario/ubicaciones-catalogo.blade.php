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

    @php
        $tiposLabel = [
            'deposito' => 'Depósito',
            'oficina' => 'Oficina',
            'vehiculo' => 'Vehículo',
            'persona' => 'Persona',
        ];
        $tiposBadge = [
            'deposito' => 'badge-secondary',
            'oficina' => 'badge-info',
            'vehiculo' => 'badge-warning',
            'persona' => 'badge-success',
        ];
    @endphp

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <input type="text" wire:model.live.debounce.400ms="busqueda"
                           class="form-control" placeholder="Buscar por nombre...">
                </div>
                <div class="col-md-4">
                    <select wire:model.live="filtroTipo" class="form-control">
                        <option value="">Todos los tipos</option>
                        @foreach ($tiposLabel as $valor => $label)
                            <option value="{{ $valor }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- FILA DE ALTA --}}
            @can('create', \App\Models\Ubicacion::class)
                <form wire:submit="agregar">
                    <div class="row align-items-start mb-3">
                        <div class="col-md-2">
                            <label class="font-weight-bold">Tipo</label>
                            <select wire:model.live="tipo" class="form-control @error('tipo') is-invalid @enderror">
                                @foreach ($tiposLabel as $valor => $label)
                                    <option value="{{ $valor }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('tipo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="font-weight-bold">
                                @switch($tipo)
                                    @case('oficina') Oficina @break
                                    @case('vehiculo') Vehículo @break
                                    @case('persona') Persona @break
                                    @default Referencia
                                @endswitch
                            </label>
                            @if ($tipo !== 'deposito')
                                <select wire:model.live="referencia_id" class="form-control @error('referencia_id') is-invalid @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach ($this->opcionesReferencia($tipo) as $opcion)
                                        <option value="{{ $opcion->id }}">
                                            @switch($tipo)
                                                @case('persona') {{ $this->formatearPersona($opcion) }} ({{ $opcion->email }}) @break
                                                @case('vehiculo') {{ $opcion->nombre_completo }} @break
                                                @default {{ $opcion->nombre }}
                                            @endswitch
                                        </option>
                                    @endforeach
                                </select>
                                @error('referencia_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            @else
                                <input type="text" class="form-control" value="No aplica" disabled>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="font-weight-bold">Nombre</label>
                            <input type="text" wire:model="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   placeholder="Ej: Depósito Central, Oficina de Guardia...">
                            @error('nombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            @if ($tipo !== 'deposito')
                                <small class="form-text text-muted">Se completa solo al elegir el registro; podés editarlo.</small>
                            @endif
                        </div>
                        <div class="col-md-3">
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
                    <thead class="thead-ops">
                        <tr>
                            <th>Nombre</th>
                            <th style="width: 25%">Tipo</th>
                            <th style="width: 12%" class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ubicaciones as $ubicacion)
                            <tr wire:key="ubicacion-{{ $ubicacion->id }}">
                                @if ($editingId === $ubicacion->id)
                                    {{-- FILA EN MODO EDICIÓN --}}
                                    <td>
                                        <input type="text" wire:model="editNombre"
                                               class="form-control form-control-sm @error('editNombre') is-invalid @enderror">
                                        @error('editNombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <select wire:model.live="editTipo" class="form-control form-control-sm mb-1 @error('editTipo') is-invalid @enderror">
                                            @foreach ($tiposLabel as $valor => $label)
                                                <option value="{{ $valor }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @if ($editTipo !== 'deposito')
                                            <select wire:model.live="editReferenciaId" class="form-control form-control-sm @error('editReferenciaId') is-invalid @enderror">
                                                <option value="">Seleccionar...</option>
                                                @foreach ($this->opcionesReferencia($editTipo) as $opcion)
                                                    <option value="{{ $opcion->id }}">
                                                        @switch($editTipo)
                                                            @case('persona') {{ $this->formatearPersona($opcion) }} @break
                                                            @case('vehiculo') {{ $opcion->nombre_completo }} @break
                                                            @default {{ $opcion->nombre }}
                                                        @endswitch
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('editReferenciaId') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                        @endif
                                        @error('editTipo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
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
                                    <td>{{ $ubicacion->nombre }}</td>
                                    <td>
                                        <span class="badge {{ $tiposBadge[$ubicacion->tipo] ?? 'badge-secondary' }}">
                                            {{ $tiposLabel[$ubicacion->tipo] ?? $ubicacion->tipo }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        @can('update', $ubicacion)
                                            <button wire:click="startEdit({{ $ubicacion->id }})"
                                                    class="btn btn-outline-secondary btn-sm" title="Editar">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        @endcan
                                        @can('delete', $ubicacion)
                                            <button wire:click="eliminar({{ $ubicacion->id }})"
                                                    wire:confirm="¿Eliminar esta ubicación?"
                                                    class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    No hay ubicaciones que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $ubicaciones->links() }}
        </div>
    </div>
</div>