<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentoRequest;
use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Documento::class);

        $documentos = Documento::with('categoria', 'subidoPor')
            ->where('activo', true)
            ->when(request('categoria_id'), fn($q) => $q->where('categoria_documento_id', request('categoria_id')))
            ->latest()
            ->get();

        $categorias = CategoriaDocumento::orderBy('nombre')->get();

        return view('admin.documentos.index', compact('documentos', 'categorias'));
    }

    public function create()
    {
        $this->authorize('create', Documento::class);
        $categorias = CategoriaDocumento::all();
        return view('admin.documentos.create', compact('categorias'));
    }

    public function store(StoreDocumentoRequest $request)
    {
        $this->authorize('create', Documento::class);

        $archivo = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();

        $categoria = CategoriaDocumento::findOrFail($request->categoria_documento_id);

        $nombreArchivo = Str::slug($request->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;

        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );

        Documento::create([
            'categoria_documento_id' => $request->categoria_documento_id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'archivo_path' => $path,
            'nombre_original' => $nombreOriginal,
            'extension' => $extension,
            'tamanio' => $archivo->getSize(),
            'subido_por' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.documentos.index')
            ->with('success', 'Documento subido correctamente.');
    }

    public function download(Documento $documento)
    {
        $this->authorize('view', $documento);

        return Storage::disk('public')->download(
            $documento->archivo_path,
            $documento->nombre_original
        );
    }

    public function destroy(Documento $documento)
    {
        $this->authorize('delete', $documento);
        $documento->delete(); // soft delete, no borra el archivo físico todavía

        return back()->with('success', 'Documento eliminado.');
    }
    public function trashed()
    {
        $this->authorize('viewAny', Documento::class);

        $documentos = Documento::onlyTrashed()
            ->with('categoria', 'subidoPor')
            ->latest('deleted_at')
            ->get();

        return view('admin.documentos.trashed', compact('documentos'));
    }

    public function restore($id)
    {
        $documento = Documento::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $documento);

        $documento->restore();

        return back()->with('success', 'Documento restaurado correctamente.');
    }

    public function forceDelete($id)
    {
        $documento = Documento::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $documento);

        Storage::disk('public')->delete($documento->archivo_path);
        $documento->forceDelete();

        return back()->with('success', 'Documento eliminado definitivamente.');
    }
    public function preview(Documento $documento)
{
    $this->authorize('view', $documento);

    if ($documento->extension !== 'pdf') {
        abort(404);
    }

    return view('admin.documentos.preview', compact('documento'));
}
}
