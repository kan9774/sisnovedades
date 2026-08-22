<div>
<x-ops-card title="Vuelos registrados" icon="plane" eyebrow="{{ $vuelos->total() }} registros">
    <x-slot name="actions">
        <button wire:click="crear" class="btn-ops btn-ops-primary btn-sm" wire:loading.attr="disabled">
            <i class="fas fa-plus-circle"></i> Registrar Vuelo
        </button>
    </x-slot>

    <form method="GET" class="mb-3">
        <div class="row align-items-center">
            <div class="col-md-4">
                <select wire:model.live="palomaFilter" class="form-control">
                    <option value="">Todas las palomas</option>
                    @foreach($palomasActivas as $p)
                        <option value="{{ $p->id }}">{{ $p->anilla }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" wire:click="$set('palomaFilter', '')" class="btn-ops btn-ops-secondary btn-sm">Limpiar filtro</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-ops-hover align-middle">
            <thead class="thead-ops">
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Palomas</th>
                    <th>Vel. media grupo</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vuelos as $vuelo)
                    <tr>
                        <td>{{ $vuelo->fecha->format('d/m/Y') }}</td>
                        <td>
                            @if ($vuelo->tipo === 'entrenamiento')
                                <span class="badge-ops badge-ops-info"><i class="fas fa-running mr-1"></i> Entrenamiento</span>
                            @else
                                <span class="badge-ops badge-ops-warning"><i class="fas fa-trophy mr-1"></i> Competición</span>
                            @endif
                        </td>
                        <td>
                            @foreach($vuelo->palomas as $p)
                                <span class="badge-ops badge-ops-secondary">{{ $p->anilla }}</span>
                            @endforeach
                        </td>
                        <td>{{ $vuelo->velocidad_promedio ?? '-' }}</td>
                        <td>
                            @if($vuelo->estado === 'en_curso')
                                <span class="badge-ops badge-ops-warning">En curso</span>
                            @else
                                <span class="badge-ops badge-ops-success">Finalizado</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="ops-actions">
                                <a href="{{ route('admin.vuelos.resultados', $vuelo) }}" class="btn-ops btn-ops-success btn-ops-icon btn-ops-icon--sm" title="Cargar resultados">
                                    <i class="fas fa-flag-checkered"></i>
                                </a>
                                <button wire:click="abrirEditar({{ $vuelo->id }})" class="btn-ops btn-ops-warning btn-ops-icon btn-ops-icon--sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if ($vuelo->palomas->count() === 0)
                                    <button wire:click="confirmarEliminacion({{ $vuelo->id }})" class="btn-ops btn-ops-danger btn-ops-icon btn-ops-icon--sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-3 text-muted">No hay vuelos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot name="footer">
        {{ $vuelos->appends(request()->query())->links() }}
    </x-slot>
</x-ops-card>

{{-- FORMULARIO: crear/editar --}}
<template x-teleport="body">
    <div class="ops-panel-overlay" id="modalVuelo" x-data x-init="$watch('$wire.showForm', value => {
        if (value) document.body.classList.add('ops-panel-open');
        else document.body.classList.remove('ops-panel-open');
    })"
        :class="{ 'is-open': $wire.showForm }" wire:click.self="cerrarForm">
        <div class="ops-panel">
            <div class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">Vuelos · Administración</span>
                        <h5 class="ops-panel__title">
                            @if ($formFormTipo === 'create')
                                Registrar Vuelo
                            @else
                                Editar Vuelo
                            @endif
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" wire:click="cerrarForm" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body" wire:loading.class="opacity-50" wire:target="guardar">
                    <div class="ops-panel__content">
                        <form wire:submit="guardar" id="form-vuelo">
                            @if ($successMsg)
                                <div class="alert alert-success">{{ $successMsg }}</div>
                            @endif
                            @if ($errorMsg)
                                <div class="alert alert-danger">{{ $errorMsg }}</div>
                            @endif

                            @if ($formFormTipo === 'edit' && $vuelo && $vuelo->estado === 'finalizado')
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Este vuelo ya fue finalizado. Podés editar los datos generales, pero la lista de palomas
                                    participantes y sus anillas de competición ya no se pueden modificar.
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label for="fecha">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" id="fecha" wire:model="formFecha"
                                        class="form-control form-control-sm @error('formFecha') is-invalid @enderror" required>
                                    @error('formFecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="vueloTipo">Tipo <span class="text-danger">*</span></label>
                                    <select id="vueloTipo" wire:model="formVueloTipo"
                                        class="form-control form-control-sm @error('formVueloTipo') is-invalid @enderror" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="entrenamiento">Entrenamiento</option>
                                        <option value="competicion">Competición</option>
                                    </select>
                                    @error('formVueloTipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="horaLiberacion">Hora de liberación</label>
                                    <input type="time" id="horaLiberacion" wire:model="formHoraLiberacion" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="puntoLiberacion">Punto de liberación</label>
                                    <input type="text" id="puntoLiberacion" wire:model="formPuntoLiberacion" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="condicionesClimaticas">Condiciones climáticas</label>
                                <textarea id="condicionesClimaticas" wire:model="formCondicionesClimaticas" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="observaciones">Observaciones generales del vuelo</label>
                                <textarea id="observaciones" wire:model="formObservaciones" class="form-control form-control-sm" rows="2"></textarea>
                            </div>

                            <hr>
                            <h5 class="my-3 text-dark"><i class="fas fa-dove mr-1"></i> Palomas participantes <span class="text-danger">*</span></h5>
                            @error('selectedPalomaIds')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle table-ops-hover">
                                    <thead class="thead-ops">
                                        <tr>
                                            <th style="width:40px;"></th>
                                            <th>Anilla</th>
                                            <th>Nombre</th>
                                            <th>Estado actual</th>
                                            <th>Anilla de competición</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($palomasActivas as $paloma)
                                            @php
                                                $checked = isset($selectedPalomaIds[$paloma->id]);
                                                $disabled = $formFormTipo === 'edit' && $vuelo && $vuelo->estado === 'finalizado';
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" class="paloma-check"
                                                        value="{{ $paloma->id }}"
                                                        {{ $checked ? 'checked' : '' }}
                                                        {{ $disabled ? 'disabled' : '' }}
                                                        wire:click="togglePaloma({{ $paloma->id }})">
                                                </td>
                                                <td>{{ $paloma->anilla }}</td>
                                                <td>-</td>
                                                <td><span class="badge-ops badge-ops-secondary">{{ $paloma->estado->nombre ?? '-' }}</span></td>
                                                <td>
                                                    <input type="text"
                                                        @if ($checked && !$disabled)
                                                            wire:model.live.debounce.300ms="palomasDatos.{{ $paloma->id }}.anilla_competicion"
                                                        @endif
                                                        class="form-control form-control-sm paloma-datos"
                                                        value="{{ $palomasDatos[$paloma->id]['anilla_competicion'] ?? '' }}"
                                                        {{ $checked && !$disabled ? '' : 'disabled' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted d-block mb-3">Distancia, hora de llegada y posición se cargan al finalizar el vuelo, desde "Cargar resultados".</small>
                        </form>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="cerrarForm"
                        wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                        Cancelar
                    </button>
                    <button type="submit" form="form-vuelo" class="btn btn-ops-primary"
                        wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                        @if ($loading)
                            <span class="spinner-border spinner-border-sm mr-1"></span>
                        @endif
                        <i class="fas fa-save"></i>
                        {{ $formFormTipo === 'create' ? 'Guardar Vuelo' : 'Actualizar Vuelo' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- FORMULARIO: confirmación de eliminación --}}
<template x-teleport="body">
    <div class="ops-panel-overlay" id="confirmDeleteModal" x-data x-init="$watch('$wire.confirmDeleteId', value => {
        if (value) document.body.classList.add('ops-panel-open');
        else document.body.classList.remove('ops-panel-open');
    })"
        :class="{ 'is-open': $wire.confirmDeleteId }"
        wire:click.self="confirmDeleteId = null">
        <div class="ops-panel ops-panel--sm">
            <div class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">Confirmar</span>
                        <h5 class="ops-panel__title">Eliminar vuelo</h5>
                    </div>
                    <button type="button" class="ops-panel__close" wire:click="confirmDeleteId = null" title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        <div class="text-center mb-3">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                        </div>
                        <p class="text-center mb-0">¿Está seguro que desea eliminar este vuelo?</p>
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="confirmDeleteId = null"
                        wire:loading.attr="disabled">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-ops-danger"
                        wire:click="ejecutarEliminacion"
                        wire:loading.attr="disabled">
                        @if ($loading)
                            <span class="spinner-border spinner-border-sm mr-1"></span>
                        @endif
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.paloma-check').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var row = this.closest('tr');
                row.querySelectorAll('.paloma-datos').forEach(function (input) {
                    input.disabled = !checkbox.checked;
                });
            });
        });
    });
</script>
</div>
