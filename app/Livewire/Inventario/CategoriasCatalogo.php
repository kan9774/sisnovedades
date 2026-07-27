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
    public ?string $codigo_abreviatura = null;

    // Edición inline (fila de la tabla)
    public ?int $editingId = null;
    public string $editNombre = '';
    public ?string $editCodigoAbreviatura = null;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150|unique:categorias,nombre',
            'codigo_abreviatura' => 'nullable|string|max:6|alpha_num|unique:categorias,codigo_abreviatura',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        'codigo_abreviatura.alpha_num' => 'Solo letras y números, sin espacios ni guiones.',
        'codigo_abreviatura.unique' => 'Ya existe una categoría con esa abreviatura.',
        'editNombre.required' => 'El nombre es obligatorio.',
        'editNombre.unique' => 'Ya existe una categoría con ese nombre.',
        'editCodigoAbreviatura.alpha_num' => 'Solo letras y números, sin espacios ni guiones.',
        'editCodigoAbreviatura.unique' => 'Ya existe una categoría con esa abreviatura.',
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

        // Si se deja en blanco, el modelo la autogenera en el evento
        // `creating` (ver Categoria::generarAbreviaturaUnica).
        Categoria::create($datos);

        session()->flash('success', 'Categoría creada.');
        $this->reset(['nombre', 'codigo_abreviatura']);
        $this->resetErrorBag();
    }

    public function startEdit(int $categoriaId): void
    {
        $categoria = Categoria::findOrFail($categoriaId);
        $this->authorize('update', $categoria);

        $this->editingId = $categoria->id;
        $this->editNombre = $categoria->nombre;
        $this->editCodigoAbreviatura = $categoria->codigo_abreviatura;
        $this->resetErrorBag();
    }

    public function saveEdit(): void
    {
        $categoria = Categoria::findOrFail($this->editingId);
        $this->authorize('update', $categoria);

        $datos = $this->validate([
            'editNombre' => 'required|string|max:150|unique:categorias,nombre,' . $this->editingId,
            'editCodigoAbreviatura' => 'nullable|string|max:6|alpha_num|unique:categorias,codigo_abreviatura,' . $this->editingId,
        ]);

        $categoria->update([
            'nombre' => $datos['editNombre'],
            'codigo_abreviatura' => $datos['editCodigoAbreviatura'],
        ]);

        session()->flash('success', 'Categoría actualizada.');
        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editNombre', 'editCodigoAbreviatura']);
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
            ->when($this->busqueda, fn($q) => $q->where(fn($q2) => $q2
                ->where('nombre', 'like', "%{$this->busqueda}%")
                ->orWhere('codigo_abreviatura', 'like', "%{$this->busqueda}%")))
            ->withCount('items')
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.inventario.categorias-catalogo', [
            'categorias' => $categorias,
        ]);
    }
}
