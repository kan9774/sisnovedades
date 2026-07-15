<?php


namespace App\Livewire\Catalogos;

use Livewire\Component;

abstract class CatalogoSimpleModal extends Component
{
    public bool $abierto = false;
    public array $items = [];
    public ?int $editandoId = null;
    public string $nombre = '';
    public bool $activo = true;

    abstract protected function modelClass(): string;
    abstract protected function eventoActualizado(): string; // ej: 'combustible-actualizado'
    abstract public function titulo(): string;

    public function mount(): void
    {
        $this->cargar();
    }

    protected function cargar(): void
    {
        $this->items = $this->modelClass()::orderBy('nombre')->get()->toArray();
    }

    public function abrir(): void
    {
        $this->resetForm();
        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editandoId = null;
        $this->nombre = '';
        $this->activo = true;
        $this->resetErrorBag();
    }

    public function editar(int $id): void
    {
        $item = $this->modelClass()::findOrFail($id);
        $this->editandoId = $item->id;
        $this->nombre = $item->nombre;
        $this->activo = $item->activo;
    }

    public function guardar(): void
    {
        $this->validate(['nombre' => 'required|string|max:100']);

        $model = $this->modelClass()::updateOrCreate(
            ['id' => $this->editandoId],
            ['nombre' => $this->nombre, 'activo' => $this->activo]
        );

        $this->cargar();
        $this->resetForm();

        // Le avisa al form del vehículo (fuera de Livewire) que hay un ítem nuevo
      $this->dispatch($this->eventoActualizado(), id: $model->id, nombre: $model->nombre);
    }

    public function eliminar(int $id): void
    {
        $this->modelClass()::findOrFail($id)->delete();
        $this->cargar();
    }
}
