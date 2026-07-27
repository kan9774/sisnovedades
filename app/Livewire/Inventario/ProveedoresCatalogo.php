<?php

namespace App\Livewire\Inventario;

use App\Models\Proveedor;
use Livewire\Component;
use Livewire\WithPagination;

class ProveedoresCatalogo extends Component
{
    use WithPagination;

    public string $busqueda = '';

    // Alta (fila superior)
    public string $nombre = '';
    public ?string $contacto = '';
    public ?string $telefono = '';

    // Edición inline (fila de la tabla)
    public ?int $editingId = null;
    public string $editNombre = '';
    public ?string $editContacto = '';
    public ?string $editTelefono = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150|unique:proveedores,nombre',
            'contacto' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:30',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.unique' => 'Ya existe un proveedor con ese nombre.',
        'editNombre.required' => 'El nombre es obligatorio.',
        'editNombre.unique' => 'Ya existe un proveedor con ese nombre.',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Proveedor::class);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function agregar(): void
    {
        $this->authorize('create', Proveedor::class);

        $datos = $this->validate();

        Proveedor::create($datos);

        session()->flash('success', 'Proveedor creado.');
        $this->reset(['nombre', 'contacto', 'telefono']);
        $this->resetErrorBag();
    }

    public function startEdit(int $proveedorId): void
    {
        $proveedor = Proveedor::findOrFail($proveedorId);
        $this->authorize('update', $proveedor);

        $this->editingId = $proveedor->id;
        $this->editNombre = $proveedor->nombre;
        $this->editContacto = $proveedor->contacto;
        $this->editTelefono = $proveedor->telefono;
        $this->resetErrorBag();
    }

    public function saveEdit(): void
    {
        $proveedor = Proveedor::findOrFail($this->editingId);
        $this->authorize('update', $proveedor);

        $datos = $this->validate([
            'editNombre' => 'required|string|max:150|unique:proveedores,nombre,' . $this->editingId,
            'editContacto' => 'nullable|string|max:150',
            'editTelefono' => 'nullable|string|max:30',
        ]);

        $proveedor->update([
            'nombre' => $datos['editNombre'],
            'contacto' => $datos['editContacto'],
            'telefono' => $datos['editTelefono'],
        ]);

        session()->flash('success', 'Proveedor actualizado.');
        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editNombre', 'editContacto', 'editTelefono']);
        $this->resetErrorBag();
    }

    public function eliminar(int $proveedorId): void
    {
        $proveedor = Proveedor::findOrFail($proveedorId);
        $this->authorize('delete', $proveedor);

        if ($proveedor->itemUnidades()->exists() || $proveedor->lotesStock()->exists()) {
            session()->flash('error', 'No se puede eliminar: hay unidades o lotes de stock asociados a este proveedor.');
            return;
        }

        $proveedor->delete();
        session()->flash('success', 'Proveedor eliminado.');
    }

    public function render()
    {
        $proveedores = Proveedor::query()
            ->when($this->busqueda, fn ($q) => $q->where('nombre', 'like', "%{$this->busqueda}%"))
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.inventario.proveedores-catalogo', [
            'proveedores' => $proveedores,
        ]);
    }
}