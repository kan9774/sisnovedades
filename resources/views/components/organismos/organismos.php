<?php

use App\Models\Organismo;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function editar(int $id): void
    {
        $organismo = Organismo::findOrFail($id);

        $this->cargarParaEditar($organismo);
    }

    protected function cargarParaEditar(Organismo $organismo): void
    {
        $this->editingId = $organismo->id;
        $this->name = $organismo->name;
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
            'name' => 'required|string|max:255|unique:organismos,name,' . $this->editingId,
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'name.unique' => 'Ya existe un organismo con ese nombre.',
        ]);

        if ($this->editingId) {
            $organismo = Organismo::findOrFail($this->editingId);

            $organismo->update($data);

            session()->flash('success', "Organismo «{$organismo->name}» actualizado correctamente.");
        } else {
            $organismo = Organismo::create($data);

            session()->flash('success', "Organismo «{$organismo->name}» creado correctamente.");
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function eliminar(int $id): void
    {
        $organismo = Organismo::withCount('novedades')->findOrFail($id);

        if ($organismo->novedades_count > 0) {
            session()->flash('error', "No se puede eliminar «{$organismo->name}»: tiene {$organismo->novedades_count} novedad(es) asociada(s).");
            return;
        }

        $nombre = $organismo->name;

        try {
            $organismo->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // Red de seguridad ante relaciones (FK) futuras no contempladas arriba.
            session()->flash('error', "No se puede eliminar «{$nombre}»: todavía tiene registros relacionados en otra parte del sistema.");
            return;
        }

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        session()->flash('success', "Organismo «{$nombre}» eliminado correctamente.");
        $this->resetPage();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function render()
    {
        $organismos = Organismo::withCount('novedades')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate(15);

        return view('components.organismos.organismos', [
            'organismos' => $organismos,
        ]);
    }
};