<?php

namespace App\Livewire\Vehiculos;

use App\Models\MantenimientoVehiculo;
use App\Models\Vehiculo;
use Livewire\Component;

class MantenimientoModal extends Component
{
    public Vehiculo $vehiculo;

    public bool $abierto = false;
    public $items = [];
    public ?int $editandoId = null;

    public string $tipo = '';
    public string $fecha = '';
    public ?int $kilometraje = null;
    public ?float $costo = null;
    public string $descripcion = '';
    public ?string $taller = null;
    public ?string $proximo_mantenimiento_fecha = null;
    public ?int $proximo_mantenimiento_km = null;

    public function mount(Vehiculo $vehiculo): void
    {
        $this->vehiculo = $vehiculo;
        $this->cargar();
    }

    protected function cargar(): void
    {
        $this->items = $this->vehiculo->mantenimientos()->latest('fecha')->get();
    }

    public function abrir(): void
    {
        $this->authorize('create', MantenimientoVehiculo::class);
        $this->resetForm();
        $this->fecha = now()->format('Y-m-d');
        $this->abierto = true;
    }

    public function editar(int $id): void
    {
        $item = MantenimientoVehiculo::findOrFail($id);
        $this->authorize('update', $item);

        $this->editandoId = $item->id;
        $this->tipo = $item->tipo;
        $this->fecha = $item->fecha->format('Y-m-d');
        $this->kilometraje = $item->kilometraje;
        $this->costo = $item->costo;
        $this->descripcion = $item->descripcion;
        $this->taller = $item->taller;
        $this->proximo_mantenimiento_fecha = $item->proximo_mantenimiento_fecha?->format('Y-m-d');
        $this->proximo_mantenimiento_km = $item->proximo_mantenimiento_km;
        $this->resetErrorBag();
        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editandoId', 'tipo', 'fecha', 'kilometraje', 'costo', 'descripcion',
            'taller', 'proximo_mantenimiento_fecha', 'proximo_mantenimiento_km',
        ]);
        $this->resetErrorBag();
    }

    public function guardar(): void
    {
        $data = $this->validate([
            'tipo' => 'required|in:preventivo,correctivo,revision_tecnica,otro',
            'fecha' => 'required|date',
            'kilometraje' => 'nullable|integer|min:0',
            'costo' => 'nullable|numeric|min:0',
            'descripcion' => 'required|string',
            'taller' => 'nullable|string|max:255',
            'proximo_mantenimiento_fecha' => 'nullable|date',
            'proximo_mantenimiento_km' => 'nullable|integer|min:0',
        ]);

        if ($this->editandoId) {
            $item = MantenimientoVehiculo::findOrFail($this->editandoId);
            $this->authorize('update', $item);
            $item->update($data);
        } else {
            $this->authorize('create', MantenimientoVehiculo::class);
            $data['registrado_por'] = auth()->id();
            $this->vehiculo->mantenimientos()->create($data);
        }

        $this->cargar();
        $this->cerrar();
    }

    public function eliminar(int $id): void
    {
        $item = MantenimientoVehiculo::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();
        $this->cargar();
    }

    public function render()
    {
        return view('livewire.vehiculos.mantenimiento-modal');
    }
}