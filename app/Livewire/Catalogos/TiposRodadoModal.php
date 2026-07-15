<?php
// app/Livewire/Catalogos/TiposRodadoModal.php
namespace App\Livewire\Catalogos;

use App\Models\TipoRodado;
use Livewire\Component;

class TiposRodadoModal extends Component
{
    public bool $abierto = false;
    public $items = [];
    public ?int $editandoId = null;

    public string $nombre = '';
    public ?string $medida = null;
    public string $posicion = 'unico';
    public ?string $marca = null;
    public ?float $presion_recomendada = null;
    public bool $activo = true;

    public function mount(): void { $this->cargar(); }

    protected function cargar(): void
    {
        $this->items = TipoRodado::orderBy('nombre')->get();
    }

    public function abrir(): void { $this->resetForm(); $this->abierto = true; }
    public function cerrar(): void { $this->abierto = false; $this->resetForm(); }

    protected function resetForm(): void
    {
        $this->reset(['editandoId', 'nombre', 'medida', 'marca', 'presion_recomendada']);
        $this->posicion = 'unico';
        $this->activo = true;
        $this->resetErrorBag();
    }

    public function editar(int $id): void
    {
        $item = TipoRodado::findOrFail($id);
        $this->editandoId = $item->id;
        $this->nombre = $item->nombre;
        $this->medida = $item->medida;
        $this->posicion = $item->posicion;
        $this->marca = $item->marca;
        $this->presion_recomendada = $item->presion_recomendada;
        $this->activo = $item->activo;
    }

    public function guardar(): void
    {
        $data = $this->validate([
            'nombre' => 'required|string|max:100',
            'medida' => 'nullable|string|max:50',
            'posicion' => 'required|in:delantero,trasero,unico',
            'marca' => 'nullable|string|max:100',
            'presion_recomendada' => 'nullable|numeric|min:0|max:999.99',
        ]);
        $data['activo'] = $this->activo;

        $model = TipoRodado::updateOrCreate(['id' => $this->editandoId], $data);

        $this->cargar();
        $this->resetForm();

        $this->dispatch('rodado-actualizado', id: $model->id, nombre: $model->nombre);
    }

    public function eliminar(int $id): void
    {
        TipoRodado::findOrFail($id)->delete();
        $this->cargar();
    }

    public function render()
    {
        return view('livewire.catalogos.tipos-rodado-modal');
    }
}