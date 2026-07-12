<?php

namespace App\Livewire\Landing;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Documentos extends Component
{
    use WithPagination;

    // Se reflejan en la URL (?buscar=...&categoria=...) sin ensuciar el
    // historial, así el usuario puede compartir/recargar con el mismo filtro.
    #[Url(history: false, as: 'buscar')]
    public string $search = '';

    #[Url(history: false, as: 'categoria')]
    public ?int $categoriaFilter = null;

    public int $perPage = 9;

    public ?int $previewDocumentoId = null;
    public bool $showPreview = false;

    protected string $paginationTheme = 'bootstrap';

    #[Computed]
    public function documentos()
    {
        return Documento::query()
            ->with('categoria')
            ->where('activo', true)
            ->when($this->categoriaFilter, fn ($q) => $q->where('categoria_documento_id', $this->categoriaFilter))
            ->when($this->search, fn ($q) => $q->where('titulo', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function categorias()
    {
        return CategoriaDocumento::query()
            ->withCount(['documentos' => fn ($q) => $q->where('activo', true)])
            ->orderBy('nombre')
            ->get();
    }

    #[Computed]
    public function previewDocumento()
    {
        if (! $this->previewDocumentoId) {
            return null;
        }

        return Documento::where('activo', true)->find($this->previewDocumentoId);
    }

    public function verDocumento(int $documentoId): void
    {
        $documento = Documento::where('activo', true)->findOrFail($documentoId);

        // Solo se previsualiza PDF; el resto se descarga directo desde la vista.
        if ($documento->extension === 'pdf') {
            $this->previewDocumentoId = $documento->id;
            $this->showPreview = true;
        }
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
        $this->previewDocumentoId = null;
    }

    public function filtrarCategoria(?int $categoriaId): void
    {
        $this->categoriaFilter = $this->categoriaFilter === $categoriaId ? null : $categoriaId;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->categoriaFilter = null;
        $this->resetPage();
    }

    public function urlArchivo(Documento $documento): string
    {
        return Storage::disk('public')->url($documento->archivo_path);
    }

    public function iconoPara(string $extension): string
    {
        return match ($extension) {
            'pdf' => 'fa-file-pdf',
            'doc', 'docx' => 'fa-file-word',
            'xls', 'xlsx' => 'fa-file-excel',
            'ppt', 'pptx' => 'fa-file-powerpoint',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'fa-file-image',
            'zip', 'rar', '7z' => 'fa-file-zipper',
            default => 'fa-file',
        };
    }

    public function tamanioFormateado(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        return number_format($bytes / 1024, 0) . ' KB';
    }

    public function render()
    {
        return view('livewire.landing.documentos');
    }
}