<div>
    {{-- ALERTAS GLOBALES --}}
    @if ($successMsg)
        <div wire:key="success-{{ md5($successMsg) }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.set('successMsg', '')
        }, 4000)"
            x-transition class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ $successMsg }}
            <button type="button" class="close" wire:click="$set('successMsg', '')">&times;</button>
        </div>
    @endif

    @if ($errorMsg)
        <div wire:key="error-{{ md5($errorMsg) }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => {
            show = false;
            $wire.set('errorMsg', '')
        }, 5000)"
            x-transition class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ $errorMsg }}
            <button type="button" class="close" wire:click="$set('errorMsg', '')">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-layer-group"></i> Centro de Documentación</h3>
            <div class="card-tools">
                <button wire:click="openTrash" class="btn btn-outline-secondary btn-sm mr-2">
                    <i class="fas fa-trash"></i> Papelera
                </button>
                <button wire:click="openCreate" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-circle"></i> Nuevo documento
                </button>
            </div>
        </div>

        <div class="card-body">
            {{-- BUSCADOR Y FILTROS --}}
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="input-group input-group-lg">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i
                                    class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-left-0"
                            placeholder="Buscar por título...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="categoriaFilter" class="form-control">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($search || $categoriaFilter)
                <div class="mb-3">
                    <button wire:click="clearFilters" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-times"></i> Limpiar filtros
                    </button>
                </div>
            @endif

            @if ($loading)
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando documentos...</p>
                </div>
            @else
                {{-- GRID DE DOCUMENTOS --}}
                @if ($documentos->count() > 0)
                    <div class="row">
                        @foreach ($documentos as $doc)
                            <div class="col-md-4 col-sm-6 mb-4">
                                <div class="card h-100 shadow-sm border">
                                    <div class="card-body d-flex flex-column p-3">
                                        <div class="d-flex align-items-start mb-2">
                                            <div class="mr-3">
                                                @if ($doc->extension === 'pdf')
                                                    <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                                @elseif(in_array($doc->extension, ['doc', 'docx']))
                                                    <i class="fas fa-file-word fa-3x text-primary"></i>
                                                @elseif(in_array($doc->extension, ['txt']))
                                                    <i class="fas fa-file-alt fa-3x text-muted"></i>
                                                @else
                                                    <i class="fas fa-file fa-3x text-secondary"></i>
                                                @endif
                                            </div>
                                            <div class="text-truncate w-100">
                                                <h6 class="font-weight-bold mb-1 text-dark text-truncate"
                                                    title="{{ $doc->titulo }}">{{ $doc->titulo }}</h6>
                                                <span class="badge badge-light border text-muted px-2 py-1">
                                                    {{ $doc->categoria->nombre ?? 'Sin categoría' }}
                                                </span>
                                            </div>
                                        </div>

                                        <p class="text-muted text-xs flex-grow-1 mb-3">
                                            {{ Str::limit($doc->descripcion ?? 'Sin descripción.', 80) }}
                                        </p>

                                        <div class="mt-auto">
                                            <div
                                                class="d-flex justify-content-between align-items-center text-xs text-muted mb-2 border-top pt-2">
                                                <span><i class="fas fa-database"></i>
                                                    {{ $doc->tamanio_legible }}</span>
                                                <span><i class="fas fa-calendar-alt"></i>
                                                    {{ $doc->created_at->format('d/m/Y') }}</span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i>
                                                    {{ $doc->subidoPor->name ?? 'Sistema' }}
                                                </small>
                                                <div class="d-flex gap-1">
                                                    @if ($doc->extension === 'pdf')
                                                        <button wire:click="openPreview({{ $doc->id }})"
                                                            class="btn btn-outline-info btn-sm" title="Previsualizar">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('admin.documentos.download', $doc->id) }}"
                                                        class="btn btn-outline-primary btn-sm" title="Descargar">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <button wire:click="openEdit({{ $doc->id }})"
                                                        class="btn btn-outline-warning btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button wire:click="confirmDelete({{ $doc->id }})"
                                                        class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- PAGINACIÓN --}}
                    <div class="mt-4">
                        {{ $documentos->links() }}
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                        <p>No se encontraron documentos.</p>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- MODAL: FORMULARIO CREAR / EDITAR --}}
    @if ($showForm)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas {{ $formTipo === 'create' ? 'fa-plus-circle' : 'fa-edit' }}"></i>
                            {{ $formTipo === 'create' ? 'Nuevo documento' : 'Editar documento' }}
                        </h5>
                        <button type="button" class="close" wire:click="closeForm" wire:loading.attr="disabled"
                            wire:target="formArchivo, submitForm" @disabled($loading)>&times;</button>
                    </div>
                    <div class="modal-body">
                        @if ($justSaved)
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                <h5 class="text-success">{{ $successMsg }}</h5>
                            </div>
                        @else
                            @if ($errorMsg)
                                <div class="alert alert-danger">{{ $errorMsg }}</div>
                            @endif
                            <form wire:submit="submitForm" id="form-documento">
                                <div class="form-group">
                                    <label>Categoría <span class="text-danger">*</span></label>
                                    <select wire:model.live="formCategoriaId"
                                        class="form-control @error('formCategoriaId') is-invalid @enderror">
                                        <option value="">Seleccionar categoría...</option>
                                        @foreach ($categorias as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('formCategoriaId')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Título <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.live="formTitulo"
                                        class="form-control @error('formTitulo') is-invalid @enderror"
                                        placeholder="Ej: Reglamento interno 2026">
                                    @error('formTitulo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Descripción</label>
                                    <textarea wire:model="formDescripcion" class="form-control" rows="3" placeholder="Descripción opcional..."></textarea>
                                    @error('formDescripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Archivo <span class="text-danger {{ $formTipo === 'edit' ? '' : '*' }}">
                                            {{ $formTipo === 'edit' ? '' : '*' }}
                                        </span></label>

                                    @if ($formTipo === 'edit' && $currentFileName)
                                        <div class="alert alert-info py-2 mb-2">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <strong>Archivo actual:</strong> {{ $currentFileName }}
                                            <a href="{{ Storage::url($currentFilePath) }}" target="_blank" class="ml-2">
                                                <i class="fas fa-download"></i> Descargar
                                            </a>
                                            <div class="mt-1">
                                                <label class="mb-0">
                                                    <input type="checkbox" wire:model="removeFile">
                                                    <span class="text-danger">Quitar este archivo</span>
                                                </label>
                                            </div>
                                            <small class="d-block text-muted mt-1">
                                                Si subís un nuevo archivo, este se eliminará automáticamente.
                                            </small>
                                        </div>
                                    @endif

                                    <input type="file" wire:model="formArchivo"
                                        class="form-control @error('formArchivo') is-invalid @enderror"
                                        accept=".pdf,.doc,.docx,.txt">
                                    <div wire:loading wire:target="formArchivo" class="mt-2 text-muted">
                                        <span class="spinner-border spinner-border-sm mr-1"></span> Subiendo archivo...
                                    </div>
                                    @error('formArchivo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    @if ($formArchivo)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <div class="d-flex align-items-center">
                                                <i
                                                    class="fas fa-file mr-2 {{ in_array($formArchivo->extension(), ['pdf']) ? 'text-danger' : 'text-primary' }}"></i>
                                                <div>
                                                    <strong>{{ $formArchivo->getClientOriginalName() }}</strong>
                                                    <br>
                                                    <small
                                                        class="text-muted">{{ number_format($formArchivo->getSize() / 1024, 1) }}
                                                        KB</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($formTipo === 'create' && !$formArchivo && !$loading)
                                        <small class="text-muted">Formatos: PDF, DOC, DOCX, TXT. Máximo 10 MB.</small>
                                    @endif
                                </div>
                            </form>
                        @endif
                    </div>
                    @if (!$justSaved)
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeForm"
                                wire:loading.attr="disabled" wire:target="formArchivo, submitForm"
                                @disabled($loading)>
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" form="form-documento" class="btn btn-primary"
                                wire:loading.attr="disabled" wire:target="formArchivo, submitForm"
                                @disabled($loading)>
                                <span wire:loading wire:target="formArchivo">
                                    <span class="spinner-border spinner-border-sm mr-1"></span> Subiendo archivo...
                                </span>
                                <span wire:loading.remove wire:target="formArchivo">
                                    @if ($loading)
                                        <span class="spinner-border spinner-border-sm mr-1"></span> Guardando...
                                    @else
                                        <i class="fas fa-save"></i>
                                        {{ $formTipo === 'create' ? 'Subir' : 'Guardar cambios' }}
                                    @endif
                                </span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: CONFIRMAR ELIMINACIÓN --}}
    @if ($confirmDeleteId)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar eliminación</h5>
                        <button type="button" class="close text-white"
                            wire:click="$set('confirmDeleteId', null)">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar este documento? Se moverá a la papelera.</p>
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

    {{-- MODAL: CONFIRMAR ELIMINACIÓN DEFINITIVA --}}
    @if ($confirmForceDeleteId)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-skull-crossbones"></i> Eliminación definitiva</h5>
                        <button type="button" class="close text-white"
                            wire:click="$set('confirmForceDeleteId', null)">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-danger font-weight-bold">¡ATENCIÓN!</p>
                        <p>Esta acción eliminará el documento y su archivo de forma permanente. No se puede deshacer.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            wire:click="$set('confirmForceDeleteId', null)">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="executeForceDelete"
                            @disabled($loading)>
                            @if ($loading)
                                <span class="spinner-border spinner-border-sm mr-1"></span> Eliminando...
                            @else
                                <i class="fas fa-skull-crossbones"></i> Eliminar definitivamente
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: PREVIEW PDF --}}
    @if ($showPreview && $previewUrl)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-eye"></i> {{ $previewDocumento->titulo }}
                        </h5>
                        <div>
                            <a href="{{ route('admin.documentos.download', $previewDocumento->id) }}"
                                class="btn btn-sm btn-primary mr-2" target="_blank">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                            <button type="button" class="close" wire:click="closePreview">&times;</button>
                        </div>
                    </div>
                    <div class="modal-body p-0" style="height: 75vh;">
                        <iframe src="{{ $previewUrl }}" width="100%" height="100%"
                            style="border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL: PAPELERA --}}
    @if ($showTrash)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-trash"></i> Papelera de documentos</h5>
                        <button type="button" class="close" wire:click="closeTrash">&times;</button>
                    </div>
                    <div class="modal-body">
                        @if ($trashed->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Categoría</th>
                                            <th>Eliminado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($trashed as $doc)
                                            <tr>
                                                <td>
                                                    <i
                                                        class="fas fa-file mr-1 {{ $doc->extension === 'pdf' ? 'text-danger' : 'text-primary' }}"></i>
                                                    {{ $doc->titulo }}
                                                </td>
                                                <td><span
                                                        class="badge bg-secondary">{{ $doc->categoria->nombre ?? '—' }}</span>
                                                </td>
                                                <td>{{ $doc->deleted_at->format('d/m/Y H:i') }}</td>
                                                <td class="text-center">
                                                    <button wire:click="restore({{ $doc->id }})"
                                                        class="btn btn-outline-success btn-sm mr-1" title="Restaurar">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button wire:click="confirmForceDelete({{ $doc->id }})"
                                                        class="btn btn-outline-danger btn-sm"
                                                        title="Eliminar definitivamente">
                                                        <i class="fas fa-skull-crossbones"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $trashed->links() }}
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-trash fa-2x mb-2"></i>
                                <p>La papelera está vacía.</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeTrash">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@once
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('documento-guardado', () => {
                setTimeout(() => {
                    @this.call('closeForm');
                }, 1500);
            });
        });
    </script>
@endonce