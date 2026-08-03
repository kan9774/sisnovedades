<?php

namespace App\Livewire\Grado;

use App\Models\Grado;
use Livewire\Component;
use Livewire\WithPagination;

class GradosCatalogo extends Component
{
    use WithPagination;

    public string $busqueda = '';

    // Alta (fila superior)
    public string $nombre = '';
    public ?int $orden = null;

    // Edición inline (fila de la tabla)
    public ?int $editingId = null;
    public string $editNombre = '';
    public ?int $editOrden = null;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100|unique:grados,nombre',
            'orden'  => 'nullable|integer|min:0',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.unique' => 'Ya existe un grado con ese nombre.',
        'editNombre.required' => 'El nombre es obligatorio.',
        'editNombre.unique' => 'Ya existe un grado con ese nombre.',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Grado::class);
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
        $this->authorize('create', Grado::class);

        $datos = $this->validate();

        Grado::create($datos);

        session()->flash('success', 'Grado creado.');
        $this->reset(['nombre', 'orden']);
        $this->resetErrorBag();
    }

    public function startEdit(int $gradoId): void
    {
        $grado = Grado::findOrFail($gradoId);
        $this->authorize('update', $grado);

        $this->editingId = $grado->id;
        $this->editNombre = $grado->nombre;
        $this->editOrden = $grado->orden;
        $this->resetErrorBag();
    }

    public function saveEdit(): void
    {
        $grado = Grado::findOrFail($this->editingId);
        $this->authorize('update', $grado);

        $datos = $this->validate([
            'editNombre' => 'required|string|max:100|unique:grados,nombre,' . $this->editingId,
            'editOrden'  => 'nullable|integer|min:0',
        ]);

        $grado->update([
            'nombre' => $datos['editNombre'],
            'orden'  => $datos['editOrden'],
        ]);

        session()->flash('success', 'Grado actualizado.');
        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editNombre', 'editOrden']);
        $this->resetErrorBag();
    }

    public function toggleActivo(int $gradoId): void
    {
        $grado = Grado::findOrFail($gradoId);
        $this->authorize('update', $grado);

        $grado->update(['activo' => ! $grado->activo]);
    }

    public function eliminar(int $gradoId): void
    {
        $grado = Grado::findOrFail($gradoId);
        $this->authorize('delete', $grado);

        if ($grado->usuarios()->exists()) {
            session()->flash('error', 'No se puede eliminar: hay personal con este grado asignado. Podés desactivarlo en su lugar.');
            return;
        }

        if ($grado->historialGrados()->exists()) {
            session()->flash('error', 'No se puede eliminar: el grado tiene movimientos en el historial (ascensos/pases). Podés desactivarlo en su lugar.');
            return;
        }

        $grado->delete();
        session()->flash('success', 'Grado eliminado.');
    }

    public function render()
    {
        $grados = Grado::query()
            ->when($this->busqueda, fn($q) => $q->where('nombre', 'like', "%{$this->busqueda}%"))
            ->withCount('usuarios')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.grados.grados-catalogo', [
            'grados' => $grados,
        ]);
    }
}
