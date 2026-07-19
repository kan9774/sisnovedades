<?php

use App\Models\Unidad;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public ?int $editingId = null;

    public string $nombre = '';
    public bool $activo = true;

    public function mount(?int $edit = null): void
    {
        // Permite llegar con el form ya cargado para editar vía ?edit={id}
        if ($edit) {
            $unidad = Unidad::find($edit);

            if ($unidad && Gate::allows('update', $unidad)) {
                $this->cargarParaEditar($unidad);
            }
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function editar(int $id): void
    {
        $unidad = Unidad::findOrFail($id);

        Gate::authorize('update', $unidad);

        $this->cargarParaEditar($unidad);
    }

    protected function cargarParaEditar(Unidad $unidad): void
    {
        $this->editingId = $unidad->id;
        $this->nombre = $unidad->nombre;
        $this->activo = (bool) $unidad->activo;
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function cancelar(): void
    {
        $this->resetForm();
    }

    public function guardar(): void
    {
        $data = $this->validate([
            'nombre' => 'required|string|max:255',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
        ]);

        if ($this->editingId) {
            $unidad = Unidad::findOrFail($this->editingId);
            Gate::authorize('update', $unidad);

            $unidad->update($data);

            session()->flash('success', "Unidad «{$unidad->nombre}» actualizada correctamente.");
        } else {
            Gate::authorize('create', Unidad::class);

            $unidad = Unidad::create($data);

            session()->flash('success', "Unidad «{$unidad->nombre}» creada correctamente.");
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function eliminar(int $id): void
    {
        $unidad = Unidad::withCount(['vehiculos', 'usuarios'])->findOrFail($id);

        Gate::authorize('delete', $unidad);

        $bloqueos = [];
        if ($unidad->vehiculos_count > 0) {
            $bloqueos[] = "{$unidad->vehiculos_count} vehículo(s)";
        }
        if ($unidad->usuarios_count > 0) {
            $bloqueos[] = "{$unidad->usuarios_count} usuario(s)";
        }

        if ($bloqueos) {
            session()->flash('error', "No se puede eliminar «{$unidad->nombre}»: tiene " . implode(' y ', $bloqueos) . ' asignado(s). Reasigná esos registros a otra unidad primero.');
            return;
        }

        $nombre = $unidad->nombre;

        try {
            $unidad->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // Red de seguridad: si en el futuro se agrega otra relación (FK) que
            // no está contemplada arriba, evitamos un error 500 crudo.
            session()->flash('error', "No se puede eliminar «{$nombre}»: todavía tiene registros relacionados en otra parte del sistema.");
            return;
        }

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        session()->flash('success', "Unidad «{$nombre}» eliminada correctamente.");
        $this->resetPage();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->activo = true;
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function render()
    {
        $unidades = Unidad::withCount(['vehiculos', 'usuarios'])
            ->when($this->search !== '', fn ($q) => $q->where('nombre', 'like', '%' . $this->search . '%'))
            ->orderBy('nombre')
            ->paginate(15);

        return view('components.unidades.unidades', [
            'unidades' => $unidades,
        ]);
    }
};