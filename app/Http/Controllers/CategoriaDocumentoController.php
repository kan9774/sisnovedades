<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoriaDocumentoController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', CategoriaDocumento::class);

        $categorias = CategoriaDocumento::withCount('documentos')->orderBy('nombre')->get();

        return view('admin.documentos.categorias.index', compact('categorias'));
    }

    public function create()
    {
        $this->authorize('create', CategoriaDocumento::class);
        return view('admin.documentos.categorias.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', CategoriaDocumento::class);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias_documentos,nombre',
            'descripcion' => 'nullable|string',
        ]);

        CategoriaDocumento::create([
            'nombre' => $validated['nombre'],
            'slug' => Str::slug($validated['nombre']),
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        return redirect()->route('admin.documentos.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(CategoriaDocumento $categoria)
    {
        $this->authorize('update', CategoriaDocumento::class);
        return view('admin.documentos.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, CategoriaDocumento $categoria)
    {
        $this->authorize('update', CategoriaDocumento::class);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('categorias_documentos', 'nombre')->ignore($categoria->id)],
            'descripcion' => 'nullable|string',
        ]);

        $slugAnterior = $categoria->slug;
        $slugNuevo = Str::slug($validated['nombre']);

        $categoria->update([
            'nombre' => $validated['nombre'],
            'slug' => $slugNuevo,
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        // Si cambió el nombre (y por ende el slug), renombramos la carpeta física
        if ($slugAnterior !== $slugNuevo) {
            $rutaAnterior = 'documentos/' . $slugAnterior;
            $rutaNueva = 'documentos/' . $slugNuevo;

            if (Storage::disk('public')->exists($rutaAnterior)) {
                Storage::disk('public')->move($rutaAnterior, $rutaNueva);

                // Actualiza los archivo_path de los documentos existentes de esta categoría
                foreach ($categoria->documentos()->withTrashed()->get() as $doc) {
                    $doc->update([
                        'archivo_path' => str_replace($rutaAnterior, $rutaNueva, $doc->archivo_path),
                    ]);
                }
            }
        }

        return redirect()->route('admin.documentos.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(CategoriaDocumento $categoria)
    {
        $this->authorize('delete', CategoriaDocumento::class);

        // Verifica que no tenga documentos, incluyendo los de la papelera
        $tieneDocumentos = $categoria->documentos()->withTrashed()->exists();

        if ($tieneDocumentos) {
            return back()->with('error', 'No se puede eliminar: la categoría tiene documentos asociados (incluso en la papelera).');
        }

        // Borra la carpeta física si existe y está vacía
        $rutaCarpeta = 'documentos/' . $categoria->slug;
        if (Storage::disk('public')->exists($rutaCarpeta)) {
            $archivos = Storage::disk('public')->allFiles($rutaCarpeta);
            if (empty($archivos)) {
                Storage::disk('public')->deleteDirectory($rutaCarpeta);
            } else {
                return back()->with('error', 'No se puede eliminar: la carpeta contiene archivos.');
            }
        }

        $categoria->delete();

        return redirect()->route('admin.documentos.categorias.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}