<section x-show="seccion === 'documentos'" x-cloak x-transition.opacity.duration.300ms class="py-5"
    wire:key="seccion-documentos">
    <div class="container">
        {{-- ============ HEADER DE SECCIÓN ============ --}}
        <div class="text-center mb-5">
            <span class="section-callsign">CANAL 05 // BIBLIOTECA</span>
            <h2 class="section-title">Biblioteca Técnica</h2>
            <p class="lead max-w-600 mx-auto">Manuales y reglamentos de consulta pública — BCOM1</p>
        </div>


        {{-- ============ BUSCADOR + FILTROS ============ --}}
        <div class="ops-doc-toolbar">
            <div class="ops-search">
                <span class="ops-search__prefix">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar por título...">
                @if ($search)
                    <button type="button" wire:click="$set('search', '')" class="ops-search__clear"
                        aria-label="Limpiar búsqueda">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>
            <div class="ops-doc-chips">
                <button type="button" wire:click="filtrarCategoria(null)"
                    class="ops-chip @if (!$this->categoriaFilter) ops-chip-active @endif">
                    Todas
                </button>
                @foreach ($this->categorias as $categoria)
                    <button type="button" wire:click="filtrarCategoria({{ $categoria->id }})"
                        class="ops-chip @if ($categoriaFilter === $categoria->id) ops-chip-active @endif">
                        {{ $categoria->nombre }}
                        <span class="ops-chip__count">{{ $categoria->documentos_count }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ============ RESULTADOS ============ --}}
        <div class="ops-doc-meta" wire:loading.remove wire:target="search,filtrarCategoria">
            <span>{{ $this->documentos->total() }} documento(s) encontrado(s)</span>
            @if ($search || $categoriaFilter)
                <button type="button" wire:click="limpiarFiltros" class="ops-doc-meta__reset">
                    <i class="fas fa-rotate-left"></i> Limpiar filtros
                </button>
            @endif
        </div>

        <div wire:loading.delay class="ops-doc-loading"
            wire:target="search,filtrarCategoria,nextPage,previousPage,gotoPage">
            <i class="fas fa-satellite-dish fa-spin"></i> Cargando archivo...
        </div>

        <div class="ops-doc-grid" wire:loading.class="ops-doc-grid--fading"
            wire:target="search,filtrarCategoria,nextPage,previousPage,gotoPage">
            @forelse ($this->documentos as $documento)
                <article class="ops-doc-card">
                    <div class="ops-doc-card__icon ops-doc-card__icon--{{ $documento->extension }}">
                        <i class="fas {{ $this->iconoPara($documento->extension) }}"></i>
                    </div>

                    <div class="ops-doc-card__body">
                        <span
                            class="ops-doc-card__categoria">{{ $documento->categoria->nombre ?? 'Sin categoría' }}</span>
                        <h3 class="ops-doc-card__titulo">{{ $documento->titulo }}</h3>
                        @if ($documento->descripcion)
                            <p class="ops-doc-card__desc">{{ Str::limit($documento->descripcion, 90) }}</p>
                        @endif
                        <div class="ops-doc-card__meta">
                            <span>{{ strtoupper($documento->extension) }}</span>
                            <span>{{ $this->tamanioFormateado($documento->tamanio) }}</span>
                            <span>{{ $documento->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <div class="ops-doc-card__actions">
                        @if ($documento->extension === 'pdf')
                            <button type="button" wire:click="verDocumento({{ $documento->id }})" class="ops-doc-btn">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                        @endif
                        <a href="{{ $this->urlArchivo($documento) }}" download="{{ $documento->nombre_original }}"
                            class="ops-doc-btn ops-doc-btn--ghost">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                    </div>
                </article>
            @empty
                <div class="ops-doc-empty">
                    <i class="fas fa-folder-open"></i>
                    <p>No se encontraron documentos con los filtros aplicados.</p>
                </div>
            @endforelse
        </div>

        @if ($this->documentos->hasPages())
            <div class="ops-doc-pagination">
                {{ $this->documentos->links() }}
            </div>
        @endif

        {{-- ============ MODAL PREVIEW PDF ============ --}}
        <div x-show="$wire.showPreview" x-cloak x-transition.opacity class="ops-doc-modal"
            @keydown.escape.window="$wire.closePreview()">
            <div class="ops-doc-modal__backdrop" wire:click="closePreview"></div>
            <div class="ops-doc-modal__panel" x-transition.scale.90>
                <div class="ops-doc-modal__header">
                    <span>{{ $this->previewDocumento->titulo ?? '' }}</span>
                    <button type="button" wire:click="closePreview" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @if ($this->previewDocumento)
                    <iframe src="{{ $this->urlArchivo($this->previewDocumento) }}"
                        class="ops-doc-modal__frame"></iframe>
                @endif
            </div>
        </div>
    </div>
</section>
