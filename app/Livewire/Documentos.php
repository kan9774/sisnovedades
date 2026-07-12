<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;

class Documentos extends Component
{
    use WithFileUploads;
    // Estado principal
    public $search = '';
    public $categoriaFilter = null;
    public $perPage = 12;
    public $page = 1;

    // Modal form
    public $showForm = false;
    public $formTipo = 'create'; // 'create' o 'edit'
    public $formCategoriaId = null;
    public $formTitulo = '';
    public $formDescripcion = '';
    public $formArchivo = null;
    public $formDocumentoId = null;

    // Modal trash
    public $showTrash = false;
    public $trashedPage = 1;

    // Modal preview
    public $showPreview = false;
    public $previewDocumento = null;
    public $previewUrl = '';

    // Acciones
    public $confirmDeleteId = null;
    public $confirmForceDeleteId = null;
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;
    public $uploadProgress = 0;
    public $justSaved = false;

    #[Computed]
    public function documentos()
    {
        $query = Documento::with(['categoria', 'subidoPor'])
            ->where('activo', true);

        if ($this->categoriaFilter) {
            $query->where('categoria_documento_id', $this->categoriaFilter);
        }

        if ($this->search) {
            $query->where('titulo', 'like', '%' . $this->search . '%');
        }

        return $query->latest()->paginate($this->perPage);
    }

    #[Computed]
    public function categorias()
    {
        return CategoriaDocumento::orderBy('nombre')->get();
    }

    public function submitForm()
    {
        $this->validate([
            'formCategoriaId' => 'required|exists:categorias_documentos,id',
            'formTitulo' => 'required|string|max:255',
            'formDescripcion' => 'nullable|string',
            'formArchivo' => $this->formTipo === 'create' ? 'required|file|max:10240' : 'nullable|file|max:10240',
        ], [
            'formCategoriaId.required' => 'Debes seleccionar una categoría.',
            'formCategoriaId.exists' => 'La categoría seleccionada no existe.',
            'formTitulo.required' => 'El título es obligatorio.',
            'formArchivo.max' => 'El archivo no puede superar los 10 MB.',
        ]);

        // Guarda extra: si el archivo todavía no terminó de subirse
        // (carrera entre el upload temporal y el submit), frenamos acá
        // en vez de dejar que explote más adelante.
        if ($this->formTipo === 'create' && ! ($this->formArchivo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)) {
            $this->errorMsg = 'El archivo todavía se está subiendo, esperá un segundo e intentá de nuevo.';
            return;
        }

        $this->loading = true;

        try {
            if ($this->formTipo === 'create') {
                $this->storeDocumento();
            } else {
                $this->updateDocumento();
            }

            $this->successMsg = $this->formTipo === 'create'
                ? 'Documento creado correctamente.'
                : 'Documento actualizado correctamente.';

            $this->justSaved = true;
            $this->page = 1;
            $this->dispatch('documento-guardado');
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    protected function storeDocumento()
    {
        $archivo = $this->formArchivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        $categoria = CategoriaDocumento::findOrFail($this->formCategoriaId);

        $nombreArchivo = Str::slug($this->formTitulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;

        // Thumbnail para imágenes
        $mimeType = $archivo->getMimeType();
        if ($mimeType && strpos($mimeType, 'image/') === 0) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.' . $extension) . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }

        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );

        Documento::create([
            'categoria_documento_id' => $this->formCategoriaId,
            'titulo' => $this->formTitulo,
            'descripcion' => $this->formDescripcion,
            'archivo_path' => $path,
            'nombre_original' => $nombreOriginal,
            'extension' => $extension,
            'tamanio' => $archivo->getSize(),
            'subido_por' => auth()->id(),
            'activo' => true,
        ]);
    }

    protected function updateDocumento()
    {
        $documento = Documento::findOrFail($this->formDocumentoId);
        $documento->update([
            'titulo' => $this->formTitulo,
            'descripcion' => $this->formDescripcion,
            'categoria_documento_id' => $this->formCategoriaId,
        ]);

        if ($this->formArchivo) {
            $archivo = $this->formArchivo;
            $extension = strtolower($archivo->getClientOriginalExtension());
            $nombreOriginal = $archivo->getClientOriginalName();
            $categoria = CategoriaDocumento::findOrFail($this->formCategoriaId);

            // Eliminar archivo anterior
            if ($documento->archivo_path) {
                Storage::disk('public')->delete($documento->archivo_path);
            }

            // Thumbnail para imágenes
            $mimeType = $archivo->getMimeType();
            if ($mimeType && strpos($mimeType, 'image/') === 0) {
                $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.' . $extension) . '.png';
                $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
            }

            $nombreArchivo = Str::slug($this->formTitulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
            $path = $archivo->storeAs(
                'documentos/' . $categoria->slug,
                $nombreArchivo,
                'public'
            );

            $documento->archivo_path = $path;
            $documento->nombre_original = $nombreOriginal;
            $documento->extension = $extension;
            $documento->tamanio = $archivo->getSize();
            $documento->save();
        }
    }

    public function openCreate()
    {
        $this->resetErrorBag();
        $this->formTipo = 'create';
        $this->formCategoriaId = $this->categoriaFilter;
        $this->formTitulo = '';
        $this->formDescripcion = '';
        $this->formArchivo = null;
        $this->formDocumentoId = null;
        $this->showForm = true;
        $this->errorMsg = '';
    }

    public function openEdit(int $documentoId)
    {
        $documento = Documento::findOrFail($documentoId);
        $this->resetErrorBag();

        $this->formTipo = 'edit';
        $this->formCategoriaId = $documento->categoria_documento_id;
        $this->formTitulo = $documento->titulo;
        $this->formDescripcion = $documento->descripcion;
        $this->formArchivo = null;
        $this->formDocumentoId = $documento->id;
        $this->showForm = true;
        $this->errorMsg = '';
    }

    public function resetForm()
    {
        $this->formCategoriaId = null;
        $this->formTitulo = '';
        $this->formDescripcion = '';
        $this->formArchivo = null;
        $this->formDocumentoId = null;
        $this->uploadProgress = 0;
        $this->justSaved = false;
        
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetErrorBag();
        $this->errorMsg = '';
    }

    // --- DELETE ---
    public function confirmDelete(int $documentoId)
    {
        $this->confirmDeleteId = $documentoId;
    }

    public function executeDelete()
    {
        $this->loading = true;
        try {
            $documento = Documento::findOrFail($this->confirmDeleteId);

            $documento->delete();
            $this->successMsg = 'Documento eliminado correctamente.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->confirmDeleteId = null;
        }
    }

    // --- RESTORE ---
    public function restore(int $documentoId)
    {
        $this->loading = true;
        try {
            $documento = Documento::onlyTrashed()->findOrFail($documentoId);
            $documento->restore();
            $this->successMsg = 'Documento restaurado correctamente.';
            $this->trashedPage = 1;
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al restaurar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // --- FORCE DELETE ---
    public function confirmForceDelete(int $documentoId)
    {
        $this->confirmForceDeleteId = $documentoId;
    }

    public function executeForceDelete()
    {
        $this->loading = true;
        try {
            $documento = Documento::onlyTrashed()->findOrFail($this->confirmForceDeleteId);

            // Eliminar archivo físico
            if ($documento->archivo_path) {
                Storage::disk('public')->delete($documento->archivo_path);
            }

            $documento->forceDelete();
            $this->successMsg = 'Documento eliminado definitivamente.';
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->confirmForceDeleteId = null;
        }
    }

    // --- PREVIEW ---
    public function openPreview(int $documentoId)
    {
        $documento = Documento::with('categoria')->findOrFail($documentoId);

        if ($documento->extension !== 'pdf') {
            $this->errorMsg = 'Solo se puede previsualizar PDF.';
            return;
        }

        $this->previewDocumento = $documento;
        $this->previewUrl = Storage::disk('public')->url($documento->archivo_path);
        $this->showPreview = true;
    }

    public function closePreview()
    {
        $this->showPreview = false;
        $this->previewDocumento = null;
        $this->previewUrl = '';
    }

    // --- TRASH ---
    public function openTrash()
    {
        $this->showTrash = true;
        $this->trashedPage = 1;
    }

    public function closeTrash()
    {
        $this->showTrash = false;
    }

    #[Computed]
    public function trashed()
    {
        return Documento::onlyTrashed()
            ->with(['categoria', 'subidoPor'])
            ->latest('deleted_at')
            ->paginate(10, ['*'], 'trashedPage');
    }

    public function updatedSearch()
    {
        $this->page = 1;
    }

    public function updatedCategoriaFilter()
    {
        $this->page = 1;
    }


    public function updated($propertyName)
    {
        // Solo valida en vivo los campos del formulario del modal,
        // y solo si el modal está abierto (para no interferir con
        // el buscador, el filtro de categoría, etc.)
        if (! $this->showForm) {
            return;
        }

        $camposFormulario = ['formCategoriaId', 'formTitulo', 'formDescripcion', 'formArchivo'];

        if (in_array($propertyName, $camposFormulario)) {
            $this->validateOnly($propertyName, $this->reglasValidacion(), $this->mensajesValidacion());
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->categoriaFilter = null;
        $this->page = 1;
    }

    public function render()
    {
        return view('livewire.documentos.index', [
            'categorias' => $this->categorias(),
            'documentos' => $this->documentos(),
            'trashed' => $this->trashed(),
        ]);
    }
    protected function reglasValidacion(): array
    {
        return [
            'formCategoriaId' => 'required|exists:categorias_documentos,id',
            'formTitulo' => 'required|string|max:255',
            'formDescripcion' => 'nullable|string',
            'formArchivo' => $this->formTipo === 'create' ? 'required|file|max:10240' : 'nullable|file|max:10240',
        ];
    }

    protected function mensajesValidacion(): array
    {
        return [
            'formCategoriaId.required' => 'Debes seleccionar una categoría.',
            'formCategoriaId.exists' => 'La categoría seleccionada no existe.',
            'formTitulo.required' => 'El título es obligatorio.',
            'formArchivo.max' => 'El archivo no puede superar los 10 MB.',
        ];
    }
}
