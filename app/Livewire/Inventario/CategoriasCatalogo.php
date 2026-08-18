<?php

namespace App\Livewire\Inventario;

use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriasCatalogo extends Component
{
    use WithPagination;

    public string $busqueda = '';

    // Alta (fila superior)
    public string $nombre = '';

    // Edición inline (fila de la tabla)
    public ?int $editingId = null;
    public string $editNombre = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150|unique:categorias,nombre',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        'editNombre.required' => 'El nombre es obligatorio.',
        'editNombre.unique' => 'Ya existe una categoría con ese nombre.',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Categoria::class);
    }
    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function agregar(): void
    {
        $this->authorize('create', Categoria::class);

        $datos = $this->validate();

        Categoria::create($datos);

        session()->flash('success', 'Categoría creada.');
        $this->reset(['nombre']);
        $this->resetErrorBag();
    }

    public function startEdit(int $categoriaId): void
    {
        $categoria = Categoria::findOrFail($categoriaId);
        $this->authorize('update', $categoria);

        $this->editingId = $categoria->id;
        $this->editNombre = $categoria->nombre;
        $this->resetErrorBag();
    }

    public function saveEdit(): void
    {
        $categoria = Categoria::findOrFail($this->editingId);
        $this->authorize('update', $categoria);

        $datos = $this->validate([
            'editNombre' => 'required|string|max:150|unique:categorias,nombre,' . $this->editingId,
        ]);

        $categoria->update([
            'nombre' => $datos['editNombre'],
        ]);

        session()->flash('success', 'Categoría actualizada.');
        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editNombre']);
        $this->resetErrorBag();
    }

    public function eliminar(int $categoriaId): void
    {
        $categoria = Categoria::findOrFail($categoriaId);
        $this->authorize('delete', $categoria);

        if ($categoria->items()->exists()) {
            session()->flash('error', 'No se puede eliminar: hay ítems del catálogo asignados a esta categoría.');
            return;
        }

        $categoria->delete();
        session()->flash('success', 'Categoría eliminada.');
    }

    public function render()
    {
        $categorias = Categoria::query()
            ->when($this->busqueda, fn($q) => $q->where('nombre', 'like', "%{$this->busqueda}%"))
            ->withCount('items')
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.inventario.categorias-catalogo', [
            'categorias' => $categorias,
        ]);
    }
}
