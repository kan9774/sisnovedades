<div>
    <x-ops-card title="Apoyos S-4" icon="hands-holding-circle"
        eyebrow="{{ $vistaActual === 'tabla' ? $totalApoyos . ' registros' : strtoupper($this->nombreMesActual()) }}">
        <x-slot name="actions">
            @can('create', \App\Models\Apoyo::class)
                <x-btn-ops variant="primary" icon="plus" wire:click="crear">
                    Nuevo Apoyo
                </x-btn-ops>
            @endcan
        </x-slot>

        {{-- TOGGLE DE VISTA --}}
        <div class="mb-3 d-flex align-items-center justify-content-between">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button"
                    class="btn {{ $vistaActual === 'tabla' ? 'btn-secondary' : 'btn-outline-secondary' }}"
                    wire:click="cambiarVista('tabla')">
                    <i class="fas fa-list"></i> Tabla
                </button>
                <button type="button"
                    class="btn {{ $vistaActual === 'calendario' ? 'btn-secondary' : 'btn-outline-secondary' }}"
                    wire:click="cambiarVista('calendario')">
                    <i class="fas fa-calendar-alt"></i> Calendario
                </button>
            </div>
        </div>

        {{-- BARRA DE FILTROS (solo vista tabla) --}}
        @if ($vistaActual === 'tabla')
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="form-control border-left-0"
                            placeholder="Buscar por solicitante o documento...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filtroTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach ($this->tiposApoyo as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filtroEstado" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        @foreach (\App\Models\Apoyo::ESTADOS as $estado)
                            <option value="{{ $estado }}">{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 text-right">
                    @if ($search || $filtroTipo !== '' || $filtroEstado !== '')
                        <button wire:click="clearFilters" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-times"></i> Limpiar filtros
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- ============================================================
             VISTA CALENDARIO
             ============================================================ --}}
        @if ($vistaActual === 'calendario')
            <div class="apoyos-calendar">
                <div class="apoyos-calendar__header">
                    <button type="button" wire:click="mesAnterior" class="apoyos-calendar__nav"
                        aria-label="Mes anterior">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="apoyos-calendar__title">{{ strtoupper($this->nombreMesActual()) }}</span>
                    <button type="button" wire:click="mesSiguiente" class="apoyos-calendar__nav"
                        aria-label="Mes siguiente">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    @if ($mes !== (int) now()->month || $anio !== (int) now()->year)
                        <button type="button" wire:click="irAHoy" class="apoyos-calendar__hoy">Hoy</button>
                    @endif
                </div>

                <div class="apoyos-calendar__weekdays">
                    <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                </div>

                <div class="apoyos-calendar__grid">
                    @foreach ($diasCalendario as $index => $dia)
                        @if (is_null($dia))
                            <div class="apoyos-calendar__day apoyos-calendar__day--empty"
                                wire:key="dia-vacio-{{ $anio }}-{{ $mes }}-{{ $index }}"></div>
                        @else
                            @php
                                $tiposDia = $apoyosPorDia[$dia] ?? [];
                                $fechaDia = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                            @endphp
                            <div wire:key="dia-{{ $anio }}-{{ $mes }}-{{ $dia }}"
                                class="apoyos-calendar__day
                                    @if ($esHoy($dia)) apoyos-calendar__day--today @endif
                                    @if (count($tiposDia) > 0) apoyos-calendar__day--has @endif"
                                @if (count($tiposDia) > 0)
                                    wire:click="seleccionarDia('{{ $fechaDia }}')"
                                    title="Ver apoyos de este día"
                                @endif>
                                <span class="apoyos-calendar__day-num">{{ str_pad($dia, 2, '0', STR_PAD_LEFT) }}</span>
                                @if (count($tiposDia) > 0)
                                    <div class="apoyos-calendar__indicators">
                                        @php $tiposArray = array_values($tiposDia); @endphp
                                        @foreach (array_slice($tiposArray, 0, 4) as $tipoInfo)
                                            <span class="apoyos-calendar__dot"
                                                style="background-color: {{ $tipoInfo['color'] }};"
                                                title="{{ $tipoInfo['nombre'] }} ({{ $tipoInfo['count'] }})">
                                            </span>
                                        @endforeach
                                        @if (count($tiposArray) > 4)
                                            <span class="apoyos-calendar__more">+{{ count($tiposArray) - 4 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Leyenda de tipos activos este mes --}}
                @php
                    $tiposEnMes = collect($apoyosPorDia)
                        ->flatMap(fn($tipos) => array_values($tipos))
                        ->unique(fn($t) => $t['nombre'])
                        ->values();
                @endphp
                @if ($tiposEnMes->isNotEmpty())
                    <div class="apoyos-calendar__legend">
                        @foreach ($tiposEnMes as $tipoInfo)
                            <span class="apoyos-calendar__legend-item">
                                <i class="apoyos-calendar__legend-dot" style="background-color: {{ $tipoInfo['color'] }};"></i>
                                {{ $tipoInfo['nombre'] }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- ============================================================
             VISTA TABLA
             ============================================================ --}}
        @if ($vistaActual === 'tabla')
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th style="width: 6%"></th>
                            <th style="width: 12%">Tipo</th>
                            <th style="width: 14%">Solicitante</th>
                            <th style="width: 10%">Desde</th>
                            <th style="width: 10%">Hasta</th>
                            <th style="width: 18%">A quien se dispuso</th>
                            <th style="width: 10%" class="text-center">Estado</th>
                            <th style="width: 12%">Registrado por</th>
                            <th style="width: 8%" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($apoyos as $apoyo)
                            <tr wire:key="apoyo-{{ $apoyo->id }}">
                                <td class="text-center">
                                    <span class="d-inline-block rounded-circle"
                                          style="width: 20px; height: 20px; background-color: {{ $apoyo->tipo->color ?? '#6c757d' }}; border: 2px solid #dee2e6;">
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $apoyo->tipo->nombre ?? '—' }}</strong>
                                </td>
                                <td>{{ $apoyo->organismo->name ?? '—' }}</td>
                                <td>{{ $apoyo->desde?->format('d/m/Y H:i') }}</td>
                                <td>{{ $apoyo->hasta?->format('d/m/Y H:i') }}</td>
                                <td>
                                    @foreach ($apoyo->unidades as $unidad)
                                        <span class="badge bg-light text-dark me-1">{{ $unidad->nombre }}</span>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    @php
                                        $colores = [
                                            'pendiente' => 'secondary',
                                            'activo' => 'primary',
                                            'cumplido' => 'success',
                                            'suspendido' => 'warning',
                                            'sin_efecto' => 'danger',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $colores[$apoyo->estado] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $apoyo->estado)) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $apoyo->registradoPor->name ?? '—' }}
                                    @if ($apoyo->registradoPor)
                                        <span class="text-muted ms-1">({{ $apoyo->registradoPor->initials() }})</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @can('update', $apoyo)
                                        <x-btn-ops variant="warning" icon="pen"
                                            wire:click="abrirEditar({{ $apoyo->id }})"
                                            size="xs" title="Editar">
                                        </x-btn-ops>
                                    @endcan
                                    @can('delete', $apoyo)
                                        <x-btn-ops variant="danger" icon="trash"
                                            wire:click="confirmDelete({{ $apoyo->id }})"
                                            size="xs" title="Eliminar">
                                        </x-btn-ops>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay apoyos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINACIÓN --}}
            @if ($apoyos->hasPages())
                <div class="card-footer bg-white border-0 pt-3">
                    {{ $apoyos->links() }}
                </div>
            @endif
        @endif
    </x-ops-card>

    {{-- MODAL: FORMULARIO CREAR / EDITAR (ops-panel overlay) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalApoyos"
         x-data
         x-init="$watch('$wire.showForm', value => {
             if (value) document.body.classList.add('ops-panel-open');
             else document.body.classList.remove('ops-panel-open');
         })"
         :class="{ 'is-open': $wire.showForm }"
         wire:click.self="cerrarForm">
        <div class="ops-panel">
            <div class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Administración</span>
                        <h5 class="ops-panel__title">
                            {{ $formTipo === 'create' ? 'Nuevo Apoyo' : 'Editar Apoyo' }}
                        </h5>
                    </div>
                    <button type="button" class="ops-panel__close" wire:click="cerrarForm"
                        title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body" wire:loading.class="opacity-50" wire:target="guardar">
                    <div class="ops-panel__content">
                        @if ($justSaved)
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h5 class="text-success">{{ $successMsg }}</h5>
                            </div>
                        @else
                            @if ($errorMsg)
                                <div class="alert alert-danger">{{ $errorMsg }}</div>
                            @endif
                            <form wire:submit="guardar" id="form-apoyo">
                                {{-- FILA 1: Tipo de apoyo | Solicitante --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tipo de apoyo <span class="text-danger">*</span></label>
                                            <select wire:model.live="formTipoId"
                                                class="form-control @error('formTipoId') is-invalid @enderror">
                                                <option value="">Seleccionar tipo...</option>
                                                @foreach ($this->tiposApoyo as $tipo)
                                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                                @endforeach
                                            </select>
                                            @error('formTipoId')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Solicitante <span class="text-danger">*</span></label>
                                            <select wire:model.live="formOrganismoId"
                                                class="form-control @error('formOrganismoId') is-invalid @enderror">
                                                <option value="">Seleccionar organismo...</option>
                                                @foreach ($this->organismos as $organismo)
                                                    <option value="{{ $organismo->id }}">{{ $organismo->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('formOrganismoId')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- FILA 2: Documento buscador | Documento texto libre --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Documento</label>
                                            @if ($formDocumentoNovedadId)
                                                @php $novedad = \App\Models\News::find($formDocumentoNovedadId); @endphp
                                                <div class="input-group input-group-sm">
                                                    <input type="text" class="form-control bg-light"
                                                        value="Nº {{ $novedad->number ?? '' }} · {{ $novedad->type ?? '' }}" disabled>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-danger"
                                                            wire:click="limpiarDocumento" title="Quitar documento">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <input type="text" wire:model.live.debounce.300ms="formDocumentoBusqueda"
                                                    class="form-control @error('formDocumentoBusqueda') is-invalid @enderror"
                                                    placeholder="Buscar por Nº de documento o tipo...">
                                                @if (strlen($formDocumentoBusqueda) >= 2)
                                                    @php
                                                        $resultados = \App\Models\News::where('number', 'like', '%' . $formDocumentoBusqueda . '%')
                                                            ->orWhere('type', 'like', '%' . $formDocumentoBusqueda . '%')
                                                            ->limit(5)
                                                            ->get();
                                                    @endphp
                                                    @if ($resultados->count())
                                                        <div class="list-group mt-1" style="position: relative; z-index: 1050;">
                                                            @foreach ($resultados as $res)
                                                                <button type="button"
                                                                    class="list-group-item list-group-item-action py-2"
                                                                    wire:click="seleccionarDocumento({{ $res->id }})">
                                                                    <small>
                                                                        <strong>Nº {{ $res->number }}</strong> · {{ $res->type }}
                                                                        @if ($res->organismo)
                                                                            <span class="text-muted">— {{ $res->organismo->name }}</span>
                                                                        @endif
                                                                    </small>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endif
                                                <small class="form-text text-muted">
                                                    Si no encontrás el documento, completá el campo de texto libre.
                                                </small>
                                                @error('formDocumentoBusqueda')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            @endif
                                        </div>
                                    </div>
                                    @if (!$formDocumentoNovedadId)
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Documento (texto libre)</label>
                                                <input type="text" wire:model="formDocumentoTexto"
                                                    class="form-control"
                                                    placeholder="Ej: Oficio 123/2026, Radio 45...">
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Desde / Hasta --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Desde <span class="text-danger">*</span></label>
                                            <input type="datetime-local" wire:model="formDesde"
                                                class="form-control @error('formDesde') is-invalid @enderror">
                                            @error('formDesde')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hasta <span class="text-danger">*</span></label>
                                            <input type="datetime-local" wire:model="formHasta"
                                                class="form-control @error('formHasta') is-invalid @enderror">
                                            @error('formHasta')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- FILA 4: Por Documento buscador | Por Documento texto libre --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Por Documento</label>
                                            @if ($formPorDocumentoNovedadId)
                                                @php $novedadPor = \App\Models\News::find($formPorDocumentoNovedadId); @endphp
                                                <div class="input-group input-group-sm">
                                                    <input type="text" class="form-control bg-light"
                                                        value="Nº {{ $novedadPor->number ?? '' }} · {{ $novedadPor->type ?? '' }}" disabled>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-danger"
                                                            wire:click="limpiarPorDocumento" title="Quitar documento">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <input type="text" wire:model.live.debounce.300ms="formPorDocumentoBusqueda"
                                                    class="form-control"
                                                    placeholder="Buscar por Nº de documento o tipo...">
                                                @if (strlen($formPorDocumentoBusqueda) >= 2)
                                                    @php
                                                        $resultadosPor = \App\Models\News::where('number', 'like', '%' . $formPorDocumentoBusqueda . '%')
                                                            ->orWhere('type', 'like', '%' . $formPorDocumentoBusqueda . '%')
                                                            ->limit(5)
                                                            ->get();
                                                    @endphp
                                                    @if ($resultadosPor->count())
                                                        <div class="list-group mt-1" style="position: relative; z-index: 1050;">
                                                            @foreach ($resultadosPor as $res)
                                                                <button type="button"
                                                                    class="list-group-item list-group-item-action py-2"
                                                                    wire:click="seleccionarPorDocumento({{ $res->id }})">
                                                                    <small>
                                                                        <strong>Nº {{ $res->number }}</strong> · {{ $res->type }}
                                                                        @if ($res->organismo)
                                                                            <span class="text-muted">— {{ $res->organismo->name }}</span>
                                                                        @endif
                                                                    </small>
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    @if (!$formPorDocumentoNovedadId)
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Por Documento (texto libre)</label>
                                                <input type="text" wire:model="formPorDocumentoTexto"
                                                    class="form-control"
                                                    placeholder="Ej: Radio 12...">
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- FILA 5: A quien se dispuso (multi-select con buscador, ancho completo) --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                        <label>A quien se dispuso <span class="text-danger">*</span></label>

                                        <div
                                            x-data="{
                                                options: @js($this->unidadesNombres),
                                                allUnits: @js($this->unidadesDisponibles->pluck('nombre', 'id')->flip()->toArray()),
                                                selected: [],
                                                search: '',
                                                open: false,
                                                highlightedIndex: -1,

                                                init() {
                                                    const mapIdToName = @js($this->unidadesMap);
                                                    this.selected = @js($this->formUnidades).map(id => mapIdToName[id]).filter(Boolean);

                                                    this.$watch('$wire.formUnidades', (ids) => {
                                                        const currentNames = ids.map(id => mapIdToName[id]).filter(Boolean);
                                                        const sortedCurrent = [...currentNames].sort().join(',');
                                                        const sortedSelected = [...this.selected].sort().join(',');
                                                        if (sortedCurrent !== sortedSelected) {
                                                            this.selected = currentNames;
                                                        }
                                                    });
                                                },

                                                get filtered() {
                                                    if (!this.search) return this.options;
                                                    const q = this.search.toLowerCase();
                                                    return this.options.filter(o => o.toLowerCase().includes(q));
                                                },

                                                toggle(option) {
                                                    const idx = this.selected.indexOf(option);
                                                    if (idx > -1) {
                                                        this.selected.splice(idx, 1);
                                                    } else {
                                                        this.selected.push(option);
                                                    }
                                                    this.syncToWire();
                                                },

                                                remove(option) {
                                                    this.selected = this.selected.filter(s => s !== option);
                                                    this.syncToWire();
                                                },

                                                clearAll() {
                                                    this.selected = [];
                                                    this.syncToWire();
                                                },

                                                syncToWire() {
                                                    const nameToId = this.allUnits;
                                                    const ids = this.selected.map(name => nameToId[name]).filter(Boolean);
                                                    this.$wire.set('formUnidades', ids, false);
                                                },

                                                toggleDropdown() {
                                                    if (!this.open) {
                                                        this.search = '';
                                                        this.highlightedIndex = -1;
                                                    }
                                                    this.open = !this.open;
                                                },

                                                close() {
                                                    this.open = false;
                                                    this.search = '';
                                                },

                                                onKeyDown(e) {
                                                    if (!this.open) {
                                                        if (e.key !== 'Enter' && e.key !== ' ') return;
                                                        this.toggleDropdown();
                                                        return;
                                                    }
                                                    if (e.key === 'Escape') {
                                                        this.close();
                                                        e.preventDefault();
                                                    } else if (e.key === 'ArrowDown') {
                                                        e.preventDefault();
                                                        this.highlightedIndex = Math.min(
                                                            this.highlightedIndex + 1,
                                                            this.filtered.length - 1
                                                        );
                                                    } else if (e.key === 'ArrowUp') {
                                                        e.preventDefault();
                                                        this.highlightedIndex = Math.max(
                                                            this.highlightedIndex - 1,
                                                            0
                                                        );
                                                    } else if (e.key === 'Enter') {
                                                        e.preventDefault();
                                                        if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filtered.length) {
                                                            this.toggle(this.filtered[this.highlightedIndex]);
                                                        }
                                                    }
                                                }
                                            }"
                                            @click.outside="close()"
                                            class="ms-wrapper"
                                        >
                                            {{-- Trigger --}}
                                            <div
                                                @click="toggleDropdown()"
                                                @keydown="onKeyDown"
                                                tabindex="0"
                                                class="ms-trigger @error('formUnidades') is-invalid @enderror"
                                                :class="{ 'is-open': open }"
                                            >
                                                <template x-if="selected.length > 0">
                                                    <template x-for="item in selected" :key="item">
                                                        <span class="ms-chip">
                                                            <span x-text="item"></span>
                                                            <button type="button" @click.stop="remove(item)" title="Quitar">
                                                                <i class="fas fa-xmark"></i>
                                                            </button>
                                                        </span>
                                                    </template>
                                                </template>

                                                <template x-if="selected.length === 0">
                                                    <span class="ms-placeholder">Seleccionar unidades...</span>
                                                </template>

                                                <span x-show="selected.length > 0" class="ms-clear-all" @click.stop="clearAll()" title="Limpiar todo">
                                                    <i class="fas fa-trash-can"></i> Limpiar
                                                </span>

                                                <i class="fas fa-chevron-down ms-toggle-icon"></i>
                                            </div>

                                            {{-- Dropdown --}}
                                            <div x-show="open" x-cloak
                                                class="ms-dropdown"
                                                @click.outside="close()"
                                            >
                                                <div class="ms-search-wrap">
                                                    <i class="fas fa-magnifying-glass"></i>
                                                    <input
                                                        type="text"
                                                        x-model="search"
                                                        placeholder="Buscar..."
                                                        class="ms-search"
                                                        @keydown.stop
                                                    />
                                                </div>

                                                <div class="ms-options-list">
                                                    <template x-for="(option, idx) in filtered" :key="option">
                                                        <div
                                                            class="ms-option"
                                                            :class="{
                                                                'is-selected': selected.includes(option),
                                                                'is-highlighted': idx === highlightedIndex
                                                            }"
                                                            @click="toggle(option)"
                                                            @mouseenter="highlightedIndex = idx"
                                                        >
                                                            <span class="ms-checkbox">
                                                                <i class="fas fa-check"></i>
                                                            </span>
                                                            <span class="ms-option-label" x-text="option"></span>
                                                        </div>
                                                    </template>

                                                    <div x-show="filtered.length === 0" class="ms-no-results">
                                                        No se encontraron unidades
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @error('formUnidades')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- FILA 6: Estado --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Estado <span class="text-danger">*</span></label>
                                            <select wire:model.live="formEstado"
                                                class="form-control @error('formEstado') is-invalid @enderror">
                                                @foreach (\App\Models\Apoyo::ESTADOS as $estado)
                                                    <option value="{{ $estado }}">{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                                                @endforeach
                                            </select>
                                            @error('formEstado')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- FILA 7: Descripción --}}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Descripción</label>
                                            <textarea wire:model="formDescripcion" class="form-control" rows="3"
                                                placeholder="Detalles del apoyo..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="ops-panel__footer">
                    @if (!$justSaved)
                        <button type="button" class="btn btn-outline-secondary"
                            wire:click="cerrarForm"
                            wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                            Cancelar
                        </button>
                        <button type="submit" form="form-apoyo" class="btn btn-ops-primary"
                            wire:loading.attr="disabled" wire:target="guardar" @disabled($loading)>
                            <i class="fas fa-save"></i>
                            {{ $formTipo === 'create' ? 'Crear' : 'Guardar' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- MODAL: REPORTE DEL DÍA (ops-panel overlay) --}}
    <template x-teleport="body">
    <div class="ops-panel-overlay" id="modalReporteDia"
         x-data
         x-init="$watch('$wire.diaSeleccionado', value => {
             if (value) document.body.classList.add('ops-panel-open');
             else document.body.classList.remove('ops-panel-open');
         })"
         :class="{ 'is-open': $wire.diaSeleccionado }"
         wire:click.self="cerrarReporteDia">
        <div class="ops-panel">
            <div class="ops-panel__form">
                <div class="ops-panel__header">
                    <div class="ops-panel__title-wrap">
                        <span class="ops-panel__eyebrow">BCOM1 · Reporte diario</span>
                        <h5 class="ops-panel__title">{{ $this->tituloDiaSeleccionado() }}</h5>
                    </div>
                    <button type="button" class="ops-panel__close" wire:click="cerrarReporteDia"
                        title="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ops-panel__body">
                    <div class="ops-panel__content">
                        @php
                            $coloresEstado = [
                                'pendiente' => 'secondary',
                                'activo' => 'primary',
                                'cumplido' => 'success',
                                'suspendido' => 'warning',
                                'sin_efecto' => 'danger',
                            ];
                        @endphp

                        @forelse ($this->apoyosDelDiaSeleccionado as $apoyo)
                            @php $posicion = $this->posicionEnRango($apoyo); @endphp
                            <div class="card mb-3" wire:key="reporte-dia-apoyo-{{ $apoyo->id }}">
                                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                                    <span class="d-inline-flex align-items-center">
                                        <span class="d-inline-block rounded-circle me-2"
                                              style="width: 14px; height: 14px; background-color: {{ $apoyo->tipo->color ?? '#6c757d' }}; border: 1.5px solid #dee2e6;">
                                        </span>
                                        <strong>{{ $apoyo->tipo->nombre ?? '—' }}</strong>
                                    </span>
                                    <span class="badge bg-{{ $coloresEstado[$apoyo->estado] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $apoyo->estado)) }}
                                    </span>
                                </div>
                                <div class="card-body py-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Solicitante</small>
                                            <span>{{ $apoyo->organismo->name ?? '—' }}</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Período</small>
                                            <span>
                                                {{ $apoyo->desde?->format('d/m/Y H:i') }} — {{ $apoyo->hasta?->format('d/m/Y H:i') }}
                                            </span>
                                            @if ($posicion)
                                                <span class="badge bg-light text-dark border ms-1"
                                                    title="Este apoyo se extiende por varios días">
                                                    día {{ $posicion['actual'] }} de {{ $posicion['total'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted d-block">A quien se dispuso</small>
                                        @forelse ($apoyo->unidades as $unidad)
                                            <span class="badge bg-light text-dark me-1">{{ $unidad->nombre }}</span>
                                        @empty
                                            <span class="text-muted">—</span>
                                        @endforelse
                                    </div>
                                    @if ($apoyo->descripcion)
                                        <div class="mt-2">
                                            <small class="text-muted d-block">Descripción</small>
                                            <span>{{ $apoyo->descripcion }}</span>
                                        </div>
                                    @endif
                                </div>
                                @can('update', $apoyo)
                                    <div class="card-footer bg-white text-end py-2">
                                        <x-btn-ops variant="warning" icon="pen"
                                            wire:click="abrirEditar({{ $apoyo->id }})"
                                            title="Editar apoyo">
                                            Editar
                                        </x-btn-ops>
                                    </div>
                                @endcan
                            </div>
                        @empty
                            <p class="text-center text-muted py-5 mb-0">
                                <i class="fas fa-calendar-day fa-2x d-block mb-2 opacity-50"></i>
                                Sin apoyos registrados en este día.
                            </p>
                        @endforelse
                    </div>
                </div>

                <div class="ops-panel__footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="cerrarReporteDia">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    </template>

    {{-- MODAL: CONFIRMAR ELIMINACIÓN --}}
    @if ($confirmDeleteId)
        <div class="modal fade show d-block" tabindex="-1"
            style="background: rgba(255, 255, 255, 0.15) !important; backdrop-filter: blur(12px) saturate(180%) !important; -webkit-backdrop-filter: blur(12px) saturate(180%) !important; border: 1px solid rgba(255, 255, 255, 0.3) !important; border-radius: 16px !important; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37) !important;"
            wire:click.self="$set('confirmDeleteId', null)" wire:keydown.escape="$set('confirmDeleteId', null)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar eliminación</h5>
                        <button type="button" class="close text-white"
                            wire:click="$set('confirmDeleteId', null)">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar este apoyo?</p>
                        <p class="text-muted small">Se eliminará de forma permanente (soft delete).</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('confirmDeleteId', null)">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="executeDelete"
                            @disabled($loading)>
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span> Eliminando...
                            @else
                                <i class="fas fa-trash"></i> Eliminar
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
    <script>
        $wire.$watch('successMsg', (valor) => {
            mostrarToast('success', valor);
        });

        $wire.$watch('errorMsg', (valor) => {
            mostrarToast('error', valor);
        });
    </script>
    @endscript
</div>
