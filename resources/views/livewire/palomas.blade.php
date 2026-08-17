<div>
    <x-ops-card title="Palomas" icon="dove" eyebrow="Módulo Palomar">
    <x-slot:actions>
        @can('create', \App\Models\Paloma::class)
        <x-btn-ops label="Nueva Paloma" icon="plus" wire:click="crear" />
        @endcan
    </x-slot:actions>

    {{-- Search bar --}}
    <div class="mb-3">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Buscar por anilla o palomar...">
        </div>
    </div>

    {{-- Feedback messages --}}
    @if ($successMsg)
        <div class="alert alert-success alert-dismissible">
            {{ $successMsg }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if ($errorMsg)
        <div class="alert alert-danger alert-dismissible">
            {{ $errorMsg }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Tabla --}}
    <div class="table-responsive">
        <table class="table table-ops-hover">
            <thead class="thead-ops">
                <tr>
                    <th>Anilla</th>
                    <th>Palomar</th>
                    <th>Sexo</th>
                    <th>Edad</th>
                    <th>Estado</th>
                    <th class="text-center" style="width: 140px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($palomas as $paloma)
                    <tr>
                        <td><strong>{{ $paloma->anilla }}</strong></td>
                        <td>{{ $paloma->palomar->nombre ?? '-' }}</td>
                        <td>
                            @if ($paloma->sexo === 'macho')
                                <span class="badge-ops badge-ops-primary"><i class="fas fa-mars mr-1"></i> Macho</span>
                            @elseif($paloma->sexo === 'hembra')
                                <span class="badge-ops badge-ops-danger"><i class="fas fa-venus mr-1"></i> Hembra</span>
                            @else
                                <span class="badge-ops badge-ops-secondary">Desconocido</span>
                            @endif
                        </td>
                        <td>
                            @if ($paloma->fecha_nacimiento)
                                @php
                                    $meses = intval($paloma->fecha_nacimiento->diffInMonths(now()));
                                    echo $meses . ' mes' . ($meses > 1 ? 'es' : '');
                                @endphp
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if ($paloma->estado)
                                <span class="badge" style="background-color: {{ $paloma->estado->color ?? '#6c757d' }}; color: #fff; padding: 5px 12px; font-weight: 500;">
                                    <i class="fas fa-circle mr-1" style="font-size: 0.5rem;"></i>
                                    {{ $paloma->estado->nombre }}
                                </span>
                            @else
                                <span class="text-muted">Sin estado</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="ops-actions">
                                <a href="{{ route('admin.palomas.show', $paloma->id) }}" class="btn-ops btn-ops-info btn-ops-icon" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update', $paloma)
                                <x-btn-ops icon="pencil" wire:click="abrirEditar({{ $paloma->id }})" tooltip="Editar" variant="warning" />
                                @endcan
                                @can('delete', $paloma)
                                <x-btn-ops icon="trash" wire:click="confirmarEliminacion({{ $paloma->id }})" tooltip="Eliminar" variant="danger" />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-dove fa-2x d-block mb-2" style="opacity: 0.3;"></i>
                            No hay palomas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $palomas->links() }}
    </div>
</x-ops-card>

{{-- MODAL: FORMULARIO CREAR / EDITAR (ops-panel overlay) --}}
<template x-teleport="body">
    <div class="ops-panel-overlay" id="modalPaloma" x-data x-init="$watch('$wire.showForm', value => {
        if (value) document.body.classList.add('ops-panel-open');
        else document.body.classList.remove('ops-panel-open');
    })"
        :class="{ 'is-open': $wire.showForm }" wire:click.self="cerrarForm">
        <div class="ops-panel">
            <div class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Administración</span>
                        <h5 class="ops-panel__title">
                            @if ($formTipo === 'create')
                                Nueva Paloma
                            @else
                                Editar Paloma
                            @endif
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" wire:click="cerrarForm" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body" wire:loading.class="opacity-50" wire:target="guardar">
                    <div class="ops-panel__content">
                        <form wire:submit="guardar" id="form-paloma">
                            {{-- Bloque 0: Palomar, Anilla, Estado --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Palomar <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm @error('formPalomarId') is-invalid @enderror" wire:model="formPalomarId" required>
                                            <option value="">Seleccionar...</option>
                                            @foreach ($palomares as $palomar)
                                                <option value="{{ $palomar->id }}">{{ $palomar->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('formPalomarId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Anilla <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm @error('formAnilla') is-invalid @enderror" wire:model="formAnilla" placeholder="Ej: P-123" required>
                                        @error('formAnilla') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Estado <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm @error('formEstadoId') is-invalid @enderror" wire:model="formEstadoId" required>
                                            <option value="">Seleccionar...</option>
                                            @foreach ($estados as $estado)
                                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @error('formEstadoId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            {{-- Bloque 1: Identificación Básica --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Sexo <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm @error('formSexo') is-invalid @enderror" wire:model="formSexo" required>
                                            <option value="desconocido" {{ old('formSexo', $formSexo) == 'desconocido' ? 'selected' : '' }}>Desconocido</option>
                                            <option value="macho" {{ old('formSexo', $formSexo) == 'macho' ? 'selected' : '' }}>Macho</option>
                                            <option value="hembra" {{ old('formSexo', $formSexo) == 'hembra' ? 'selected' : '' }}>Hembra</option>
                                        </select>
                                        @error('formSexo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            {{-- Bloque 2: Nacimiento --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha de nacimiento</label>
                                        <input type="date" class="form-control form-control-sm @error('formFechaNacimiento') is-invalid @enderror" wire:model="formFechaNacimiento">
                                        @error('formFechaNacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                {{-- Bloque 3: Estado Sanitario --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Estado Sanitario <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm @error('formEstadoSanitario') is-invalid @enderror" wire:model="formEstadoSanitario" required>
                                            <option value="Bien" {{ old('formEstadoSanitario', $formEstadoSanitario) == 'Bien' ? 'selected' : '' }}>Bien</option>
                                            <option value="Enferma" {{ old('formEstadoSanitario', $formEstadoSanitario) == 'Enferma' ? 'selected' : '' }}>Enferma</option>
                                        </select>
                                        @error('formEstadoSanitario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            {{-- Bloque 4: Observaciones --}}
                            <div class="form-group">
                                <label>Observaciones</label>
                                <textarea class="form-control form-control-sm @error('formObservaciones') is-invalid @enderror" wire:model="formObservaciones" rows="3" placeholder="Observaciones adicionales..."></textarea>
                                @error('formObservaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if ($successMsg)
                                <div class="alert alert-success">{{ $successMsg }}</div>
                            @endif
                            @if ($errorMsg)
                                <div class="alert alert-danger">{{ $errorMsg }}</div>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="cerrarForm"
                        wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                        Cancelar
                    </button>
                    <button type="submit" form="form-paloma" class="btn btn-ops-primary"
                        wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                        @if ($loading)
                            <span class="spinner-border spinner-border-sm mr-1"></span>
                        @endif
                        <i class="fas fa-save"></i>
                        {{ $formTipo === 'create' ? 'Crear Paloma' : 'Guardar Cambios' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
</div>
