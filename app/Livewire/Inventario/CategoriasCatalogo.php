<?php

namespace App\Livewire\Inventario;

use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriasCatalogo extends Component
{
    use WithPagination;

    public string $busqueda = '';

    public bool $mostrarModal = false;
    public ?int $categoriaId = null;

    public string $nombre = '';
    public ?string $descripcion = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150|unique:categorias,nombre,' . $this->categoriaId,
            'descripcion' => 'nullable|string|max:500',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.unique' => 'Ya existe una categoría con ese nombre.',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Categoria::class);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function abrirModalCrear(): void
    {
        $this->authorize('create', Categoria::class);
        $this->resetFormulario();
        $this->mostrarModal = true;
    }

    public function abrirModalEditar(int $categoriaId): void
    {
        $categoria = Categoria::findOrFail($categoriaId);
        $this->authorize('update', $categoria);

        $this->categoriaId = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->descripcion = (string) $categoria->descripcion;

        $this->mostrarModal = true;
    }

    public function guardar(): void
    {
        $this->categoriaId
            ? $this->authorize('update', Categoria::findOrFail($this->categoriaId))
            : $this->authorize('create', Categoria::class);

        $datos = $this->validate();

        Categoria::updateOrCreate(['id' => $this->categoriaId], $datos);

        session()->flash('success', $this->categoriaId ? 'Categoría actualizada.' : 'Categoría creada.');

        $this->cerrarModal();
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

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->resetFormulario();
    }

    private function resetFormulario(): void
    {
        $this->reset(['categoriaId', 'nombre', 'descripcion']);
        $this->resetErrorBag();
    }

    public function render()
    {
        $categorias = Categoria::query()
            ->when($this->busqueda, fn ($q) => $q->where('nombre', 'like', "%{$this->busqueda}%"))
            ->withCount('items')
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.inventario.categorias-catalogo', [
            'categorias' => $categorias,
        ]);
    }
}