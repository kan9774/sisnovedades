<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use Livewire\Component;
use Livewire\Attributes\Computed;

class CategoriasDocumentos extends Component
{
    // Alta (fila de arriba)
    public $nombre = '';
    public $descripcion = '';

    // Edición inline (por fila)
    public $editingId = null;
    public $editNombre = '';
    public $editDescripcion = '';

    // Feedback
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    #[Computed]
    public function categorias()
    {
        // Ajustá 'documentos' si el nombre de la relación en el modelo es distinto
        return CategoriaDocumento::withCount('documentos')
            ->orderBy('nombre')
            ->get();
    }

    // --- ALTA ---
    public function agregar()
    {
        $this->authorize('create', CategoriaDocumento::class);

        $this->validate([
            'nombre' => 'required|string|max:255|unique:categorias_documentos,nombre',
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);

        $this->loading = true;

        try {
            CategoriaDocumento::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]);

            $this->successMsg = 'Categoría agregada correctamente.';
            $this->reset(['nombre', 'descripcion']);
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // --- EDICIÓN INLINE ---
    public function startEdit(int $categoriaId)
    {
        $categoria = CategoriaDocumento::findOrFail($categoriaId);
        $this->authorize('update', $categoria);

        $this->resetErrorBag();
        $this->editingId = $categoria->id;
        $this->editNombre = $categoria->nombre;
        $this->editDescripcion = $categoria->descripcion;
    }

    public function cancelEdit()
    {
        $this->editingId = null;
        $this->editNombre = '';
        $this->editDescripcion = '';
        $this->resetErrorBag();
    }

    public function saveEdit()
    {
        $categoria = CategoriaDocumento::findOrFail($this->editingId);
        $this->authorize('update', $categoria);

        $this->validate([
            'editNombre' => 'required|string|max:255|unique:categorias_documentos,nombre,' . $this->editingId,
            'editDescripcion' => 'nullable|string',
        ], [
            'editNombre.required' => 'El nombre es obligatorio.',
            'editNombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);

        $this->loading = true;

        try {
            $categoria->update([
                'nombre' => $this->editNombre,
                'descripcion' => $this->editDescripcion,
            ]);

            $this->successMsg = 'Categoría actualizada correctamente.';
            $this->cancelEdit();
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al actualizar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // --- ELIMINAR (con wire:confirm en el botón, sin modal) ---
    public function eliminar(int $categoriaId)
    {
        $categoria = CategoriaDocumento::withCount('documentos')->findOrFail($categoriaId);
        $this->authorize('delete', $categoria);

        if ($categoria->documentos_count > 0) {
            $this->errorMsg = 'No se puede eliminar: la categoría tiene documentos asociados.';
            return;
        }

        $categoria->delete();
        $this->successMsg = 'Categoría eliminada correctamente.';
    }

    public function render()
    {
        return view('livewire.categorias-documentos.index', [
            'categorias' => $this->categorias(),
        ]);
    }
}