<?php

namespace App\Livewire\Inventario;

use App\Models\Talla;
use Livewire\Component;
use Livewire\WithPagination;

class TallasCatalogo extends Component
{
    use WithPagination;

    public string $busqueda = '';

    // Fila de alta
    public string $valor = '';
    public ?string $sistema = null;
    public ?int $orden = null;

    // Fila en modo edición
    public ?int $editingId = null;
    public string $editValor = '';
    public ?string $editSistema = null;
    public ?int $editOrden = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Talla::class);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------
    | Alta (fila superior)
    |--------------------------------------------------------------------
    */

    public function agregar(): void
    {
        $this->authorize('create', Talla::class);

        $datos = $this->validate([
            'valor' => 'required|string|max:20',
            'sistema' => 'nullable|string|max:30',
            'orden' => 'nullable|integer|min:0',
        ], [
            'valor.required' => 'El valor es obligatorio (ej: "M", "42").',
        ]);

        Talla::create($datos);

        session()->flash('success', 'Talla creada.');

        $this->reset(['valor', 'sistema', 'orden']);
        $this->resetErrorBag();
    }

    /*
    |--------------------------------------------------------------------
    | Edición inline (fila de la tabla)
    |--------------------------------------------------------------------
    */

    public function startEdit(int $tallaId): void
    {
        $talla = Talla::findOrFail($tallaId);
        $this->authorize('update', $talla);

        $this->editingId = $talla->id;
        $this->editValor = $talla->valor;
        $this->editSistema = $talla->sistema;
        $this->editOrden = $talla->orden;
        $this->resetErrorBag();
    }

    public function saveEdit(): void
    {
        $talla = Talla::findOrFail($this->editingId);
        $this->authorize('update', $talla);

        $datos = $this->validate([
            'editValor' => 'required|string|max:20',
            'editSistema' => 'nullable|string|max:30',
            'editOrden' => 'nullable|integer|min:0',
        ], [
            'editValor.required' => 'El valor es obligatorio (ej: "M", "42").',
        ]);

        $talla->update([
            'valor' => $datos['editValor'],
            'sistema' => $datos['editSistema'],
            'orden' => $datos['editOrden'],
        ]);

        session()->flash('success', 'Talla actualizada.');

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editValor', 'editSistema', 'editOrden']);
        $this->resetErrorBag();
    }

    /*
    |--------------------------------------------------------------------
    | Baja
    |--------------------------------------------------------------------
    */

    public function eliminar(int $tallaId): void
    {
        $talla = Talla::findOrFail($tallaId);
        $this->authorize('delete', $talla);

        if ($talla->items()->exists()) {
            session()->flash('error', 'No se puede eliminar: hay ítems del catálogo con esta talla asignada.');
            return;
        }

        $talla->delete();
        session()->flash('success', 'Talla eliminada.');
    }

    public function render()
    {
        $tallas = Talla::query()
            ->when($this->busqueda, fn ($q) => $q->where('valor', 'like', "%{$this->busqueda}%")
                ->orWhere('sistema', 'like', "%{$this->busqueda}%"))
            ->withCount('items')
            ->orderBy('orden')
            ->orderBy('valor')
            ->paginate(15);

        return view('livewire.inventario.tallas-catalogo', [
            'tallas' => $tallas,
        ]);
    }
}