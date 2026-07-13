<section x-show="seccion === 'novedades-cerradas'" x-cloak x-transition.opacity.duration.300ms
         class="py-5" wire:key="seccion-novedades-cerradas">
    <div class="container">

        {{-- ============================================================
             HEADER
             ============================================================ --}}
        <div class="text-center mb-5">
            <span class="section-callsign">CANAL 06 // NOVEDADES CERRADAS</span>
            <h2 class="section-title">Archivo de Novedades</h2>
            <p class="lead max-w-600 mx-auto">
                Guardias finalizadas — consulta pública con adjuntos y documentos
            </p>
        </div>

        {{-- ============================================================
             BUSCADOR + TOGGLE DE VISTA (solo cuando NO hay panel abierto)
             ============================================================ --}}
        <div x-show="!$wire.showPanel" x-transition class="mb-4">
            <div class="ops-doc-toolbar">
                <div class="ops-search">
                    <span class="ops-search__prefix"><i class="fas fa-search"></i></span>
                    <input type="text" wire:model.live.debounce.400ms="search"
                           placeholder="Buscar por texto, número o asunto...">
                    @if ($search)
                        <button type="button" wire:click="$set('search', '')" class="ops-search__clear"
                                aria-label="Limpiar búsqueda">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                </div>
                <div class="ops-view-toggle">
                    <button type="button" wire:click="cambiarVista('lista')"
                        class="ops-view-btn @if($vista === 'lista') ops-view-btn-active @endif">
                        <i class="fas fa-list"></i> Lista
                    </button>
                    <button type="button" wire:click="cambiarVista('calendario')"
                        class="ops-view-btn @if($vista === 'calendario') ops-view-btn-active @endif">
                        <i class="fas fa-calendar-alt"></i> Calendario
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================
             VISTA CALENDARIO
             ============================================================ --}}
        <div x-show="!$wire.showPanel && $wire.vista === 'calendario'" x-transition class="mb-4">
            <div class="ops-calendar">
                <div class="ops-calendar__header">
                    <button type="button" wire:click="mesAnterior" class="ops-calendar__nav" aria-label="Mes anterior">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="ops-calendar__title">{{ strtoupper($this->nombreMesActual()) }}</span>
                    <button type="button" wire:click="mesSiguiente" class="ops-calendar__nav" aria-label="Mes siguiente">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="button" wire:click="irAHoy" class="ops-calendar__hoy">Hoy</button>
                </div>

                <div class="ops-calendar__weekdays">
                    <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                </div>

                <div class="ops-calendar__grid">
                    @foreach ($diasCalendario as $dia)
                        @if (is_null($dia))
                            <div class="ops-calendar__day ops-calendar__day--empty"></div>
                        @else
                            @php $guardiaDia = $guardiasDelMes->get($dia); @endphp
                            <button type="button"
                                @if($guardiaDia) wire:click="verGuardia({{ $guardiaDia->id }})" @else disabled @endif
                                class="ops-calendar__day
                                    @if($guardiaDia) ops-calendar__day--closed @endif
                                    @if($this->esHoy($dia)) ops-calendar__day--today @endif">
                                <span class="ops-calendar__day-num">{{ str_pad($dia, 2, '0', STR_PAD_LEFT) }}</span>
                                @if($guardiaDia)
                                    <span class="ops-calendar__day-dot"></span>
                                @endif
                            </button>
                        @endif
                    @endforeach
                </div>

                <div class="ops-calendar__legend">
                    <span><i class="ops-calendar__legend-dot"></i> Guardia cerrada</span>
                    <span class="text-muted">Días sin guardia cerrada no son seleccionables</span>
                </div>
            </div>
        </div>

        {{-- ============================================================
             VISTA 1: GRID DE GUARDIAS CERRADAS
             ============================================================ --}}
        <div x-show="!$wire.showPanel && $wire.vista === 'lista'" x-transition>
            @if ($guardias->count() > 0)
                <div class="ops-doc-grid">
                    @foreach ($guardias as $guardia)
                        <article class="ops-doc-card ops-guardia-card">
                            <div class="ops-doc-card__icon ops-doc-card__icon--guardia">
                                <i class="fas fa-shield-alt"></i>
                            </div>

                            <div class="ops-doc-card__body">
                                <h3 class="ops-doc-card__titulo">
                                    Guardia del {{ $guardia->date->format('d/m/Y') }}
                                </h3>

                                <div class="ops-doc-card__meta">
                                    <span><i class="fas fa-user-shield"></i> {{ $guardia->capitan->grade }} {{ $guardia->capitan->name }}</span>
                                    <span><i class="fas fa-user-tie"></i> {{ $guardia->oficial->grade }} {{ $guardia->oficial->name }}</span>
                                    <span><i class="fas fa-newspaper"></i> {{ $guardia->novedades_count }} novedades</span>
                                    <span><i class="fas fa-clipboard-list"></i> {{ $guardia->novedades_personal_count ?? 0 }} personal</span>
                                </div>
                            </div>

                            <div class="ops-doc-card__actions">
                                <button type="button" wire:click="verGuardia({{ $guardia->id }})" class="ops-doc-btn ops-doc-btn--primary">
                                    <i class="fas fa-eye"></i> Ver guardia completa
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($guardias->hasPages())
                    <div class="ops-doc-pagination">{{ $guardias->links() }}</div>
                @endif
            @else
                <div class="ops-doc-empty">
                    <i class="fas fa-archive"></i>
                    <p>No hay guardias cerradas disponibles.</p>
                </div>
            @endif
        </div>

        {{-- ============================================================
             VISTA 2: PANEL DE GUARDIA (se abre al seleccionar)
             ============================================================ --}}
        @if($guardia)
        <div x-show="$wire.showPanel" x-cloak x-transition class="guardia-panel-wrapper">

            {{-- Header del panel --}}
            <div class="guardia-panel-header">
                <button type="button" wire:click="cerrarPanel" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </button>
                <h4 class="mb-0">
                    <i class="fas fa-shield-alt"></i>
                    Guardia del {{ $guardia?->date?->format('d/m/Y') }}
                    <small class="text-muted">
                        — {{ optional($guardia->capitan)->grade }} {{ optional($guardia->capitan)->name }} (Cap.) /
                        {{ optional($guardia->oficial)->grade }} {{ optional($guardia->oficial)->name }} (Of.)
                    </small>
                </h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-primary"
                        onclick="window.open('{{ optional($guardia)->id ? route('guardias-publicas.pdf-preview', $guardia->id) : '#' }}', '_blank')">
                        <i class="fas fa-download"></i> Descargar PDF
                    </button>
                </div>
            </div>

            {{-- Tabs del panel --}}
            <div class="guardia-panel-tabs">
                <button type="button" wire:click="cambiarTab('pdf')"
                    class="guardia-tab @if($panelTab === 'pdf') guardia-tab-active @endif">
                    <i class="fas fa-file-pdf"></i> PDF Preview
                </button>
                <button type="button" wire:click="cambiarTab('recibidos')"
                    class="guardia-tab @if($panelTab === 'recibidos') guardia-tab-active @endif">
                    <i class="fas fa-arrow-down"></i> Recibidos
                    @if(count($adjuntosRecibidos) > 0)
                        <span class="badge badge-count">{{ collect($adjuntosRecibidos)->flatten()->count() }}</span>
                    @endif
                </button>
                <button type="button" wire:click="cambiarTab('expedidos')"
                    class="guardia-tab @if($panelTab === 'expedidos') guardia-tab-active @endif">
                    <i class="fas fa-arrow-up"></i> Expedidos
                    @if(count($adjuntosExpedidos) > 0)
                        <span class="badge badge-count">{{ collect($adjuntosExpedidos)->flatten()->count() }}</span>
                    @endif
                </button>
            </div>

            {{-- ============================================================
                 TAB: PDF PREVIEW
                 ============================================================ --}}
            <div x-show="$wire.panelTab === 'pdf'" class="guardia-panel-content">
                <iframe src="{{ optional($guardia)->id ? route('guardias-publicas.pdf-preview', $guardia->id) : '#' }}"
                        class="guardia-pdf-frame guardia-pdf-frame--desktop"
                        title="Preview de guardia del {{ $guardia?->date?->format('d/m/Y') }}">
                </iframe>

                <div class="guardia-pdf-mobile-fallback">
                    <i class="fas fa-file-pdf"></i>
                    <p>La vista previa completa no está disponible en móvil.</p>
                    <a href="{{ optional($guardia)->id ? route('guardias-publicas.pdf-preview', $guardia->id) : '#' }}"
                       target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Abrir PDF completo
                    </a>
                </div>
            </div>

            {{-- ============================================================
                 TAB: RECIBIDOS (con adjuntos)
                 ============================================================ --}}
            <div x-show="$wire.panelTab === 'recibidos'" class="guardia-panel-content guardia-adjuntos-content">
                @if (count($adjuntosRecibidos) > 0)
                    @foreach ($adjuntosRecibidos as $tipo => $adjuntos)
                        <div class="adjunto-seccion">
                            <h5 class="adjunto-seccion-titulo">
                                <i class="fas {{ $this->tipoIcon($tipo) }}"></i>
                                {{ $tipo }} — Recibidos
                            </h5>

                            <div class="adjunto-novedades">
                                @php
                                    $novedadesTipo = optional($guardia)->novedades ?? collect();
                                    $novedadesTipo = $novedadesTipo->where('direction', 'Recibido')->where('type', $tipo);
                                @endphp
                                @foreach ($novedadesTipo as $novedad)
                                    <div class="adjunto-novedad-item">
                                        <div class="adjunto-novedad-header">
                                            <span class="adjunto-novedad-num">
                                                Nº {{ $novedad->number }}
                                                @if($novedad->affair) — {{ $novedad->affair }} @endif
                                            </span>
                                            <span class="adjunto-novedad-hora">
                                                <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($novedad->time)->format('Hi') }}
                                            </span>
                                            @if ($novedad->organismo)
                                                <span class="adjunto-novedad-organismo">
                                                    <i class="fas fa-building"></i> {{ $novedad->organismo->name }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="adjunto-novedad-texto">
                                            {{ $novedad->text }}
                                        </div>

                                        @if ($novedad->adjuntos->count() > 0)
                                            <div class="adjunto-lista">
                                                <small class="text-muted"><i class="fas fa-paperclip"></i> Adjuntos:</small>
                                                @foreach ($novedad->adjuntos as $adj)
                                                    <button type="button" wire:click="abrirAdjunto({{ $adj->id }})"
                                                            class="adjunto-item">
                                                        <i class="fas {{ $this->tipoAdjuntoIcon($adj->file_type) }}"></i>
                                                        <span>{{ $adj->file_name }}</span>
                                                        <small>{{ number_format($adj->file_size / 1024, 1) }} KB</small>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No hay adjuntos en la carpeta de Recibidos para esta guardia.</p>
                    </div>
                @endif
            </div>

            {{-- ============================================================
                 TAB: EXPEDIDOS (con adjuntos)
                 ============================================================ --}}
            <div x-show="$wire.panelTab === 'expedidos'" class="guardia-panel-content guardia-adjuntos-content">
                @if (count($adjuntosExpedidos) > 0)
                    @foreach ($adjuntosExpedidos as $tipo => $adjuntos)
                        <div class="adjunto-seccion">
                            <h5 class="adjunto-seccion-titulo">
                                <i class="fas {{ $this->tipoIcon($tipo) }}"></i>
                                {{ $tipo }} — Expedidos
                            </h5>

                            @php
                                $novedadesTipo = optional($guardia)->novedades ?? collect();
                                $novedadesTipo = $novedadesTipo->where('direction', 'Expedido')->where('type', $tipo);
                            @endphp
                            @foreach ($novedadesTipo as $novedad)
                                <div class="adjunto-novedad-item">
                                    <div class="adjunto-novedad-header">
                                        <span class="adjunto-novedad-num">
                                            Nº {{ $novedad->number }}
                                            @if($novedad->affair) — {{ $novedad->affair }} @endif
                                        </span>
                                        <span class="adjunto-novedad-hora">
                                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($novedad->time)->format('Hi') }}
                                        </span>
                                        @if ($novedad->destino)
                                            <span class="adjunto-novedad-destino">
                                                <i class="fas fa-paper-plane"></i> {{ $novedad->destino }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="adjunto-novedad-texto">
                                        {{ $novedad->text }}
                                    </div>

                                    @if ($novedad->adjuntos->count() > 0)
                                        <div class="adjunto-lista">
                                            <small class="text-muted"><i class="fas fa-paperclip"></i> Adjuntos:</small>
                                            @foreach ($novedad->adjuntos as $adj)
                                                <button type="button" wire:click="abrirAdjunto({{ $adj->id }})"
                                                        class="adjunto-item">
                                                    <i class="fas {{ $this->tipoAdjuntoIcon($adj->file_type) }}"></i>
                                                    <span>{{ $adj->file_name }}</span>
                                                    <small>{{ number_format($adj->file_size / 1024, 1) }} KB</small>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No hay adjuntos en la carpeta de Expedidos para esta guardia.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============================================================
             MODAL: VISTA DE ADJUNTO INDIVIDUAL
             ============================================================ --}}
        <div x-show="$wire.showAdjunto" x-cloak x-transition.opacity
             class="adjunto-modal-overlay"
             @keydown.escape.window="$wire.cerrarAdjunto()">
            <div class="adjunto-modal-backdrop" wire:click="cerrarAdjunto"></div>
            <div class="adjunto-modal-panel" x-transition.scale.95>
                <div class="adjunto-modal-header">
                    <div>
                        <strong>{{ $adjuntoData['name'] ?? '' }}</strong>
                        <span class="adjunto-modal-meta">
                            {{ $adjuntoData['novedad_number'] ?? '' }} — {{ $adjuntoData['novedad_direction'] ?? '' }}
                        </span>
                    </div>
                    <button type="button" wire:click="cerrarAdjunto" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="adjunto-modal-body">
                    @if ($adjuntoData['is_image'] ?? false)
                        <img src="{{ $adjuntoData['url'] ?? '#' }}" class="adjunto-preview-img" alt="Adjunto">
                    @elseif ($adjuntoData['is_pdf'] ?? false)
                        <iframe src="{{ $adjuntoData['url'] ?? '#' }}" class="adjunto-preview-frame adjunto-preview-frame--desktop"></iframe>
                        <div class="adjunto-pdf-mobile-fallback">
                            <i class="fas fa-file-pdf"></i>
                            <p>La vista previa completa no está disponible en móvil.</p>
                            <a href="{{ $adjuntoData['url'] ?? '#' }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt"></i> Abrir PDF completo
                            </a>
                        </div>
                    @else
                        <div class="adjunto-preview-not-supported">
                            <i class="fas fa-file"></i>
                            <p>Este tipo de archivo no se puede previsualizar.</p>
                            <a href="{{ $adjuntoData['url'] ?? '#' }}" target="_blank" class="btn btn-primary mt-3">
                                <i class="fas fa-download"></i> Descargar archivo
                            </a>
                        </div>
                    @endif
                </div>
                <div class="adjunto-modal-footer">
                    <a href="{{ $adjuntoData['url'] ?? '#' }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-download"></i> Descargar
                    </a>
                    <button type="button" wire:click="cerrarAdjunto" class="btn btn-secondary">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div>
</section>