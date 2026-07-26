<?php

namespace App\Livewire\Inventario;

use App\Models\ItemUnidad;
use App\Models\Movimiento;
use App\Models\Stock;
use App\Models\Ubicacion;
use Livewire\Component;
use Livewire\WithPagination;

class UbicacionesCatalogo extends Component
{
    use WithPagination;

    public string $busqueda = '';

    public bool $mostrarModal = false;
    public ?int $ubicacionId = null;

    public string $nombre = '';
    public ?string $descripcion = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150|unique:ubicaciones,nombre,' . $this->ubicacionId,
            'descripcion' => 'nullable|string|max:500',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.unique' => 'Ya existe una ubicación con ese nombre.',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Ubicacion::class);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function abrirModalCrear(): void
    {
        $this->authorize('create', Ubicacion::class);
        $this->resetFormulario();
        $this->mostrarModal = true;
    }

    public function abrirModalEditar(int $ubicacionId): void
    {
        $ubicacion = Ubicacion::findOrFail($ubicacionId);
        $this->authorize('update', $ubicacion);

        $this->ubicacionId = $ubicacion->id;
        $this->nombre = $ubicacion->nombre;
        $this->descripcion = (string) $ubicacion->descripcion;

        $this->mostrarModal = true;
    }

    public function guardar(): void
    {
        $this->ubicacionId
            ? $this->authorize('update', Ubicacion::findOrFail($this->ubicacionId))
            : $this->authorize('create', Ubicacion::class);

        $datos = $this->validate();

        Ubicacion::updateOrCreate(['id' => $this->ubicacionId], $datos);

        session()->flash('success', $this->ubicacionId ? 'Ubicación actualizada.' : 'Ubicación creada.');

        $this->cerrarModal();
    }

    public function eliminar(int $ubicacionId): void
    {
        $ubicacion = Ubicacion::findOrFail($ubicacionId);
        $this->authorize('delete', $ubicacion);

        $enUso = Stock::where('ubicacion_id', $ubicacion->id)->where('cantidad', '>', 0)->exists()
            || Movimiento::where('ubicacion_origen_id', $ubicacion->id)
                ->orWhere('ubicacion_destino_id', $ubicacion->id)
                ->exists()
            || ItemUnidad::where('ubicacion_actual_id', $ubicacion->id)->exists();

        if ($enUso) {
            session()->flash('error', 'No se puede eliminar: la ubicación tiene stock, movimientos o unidades asociadas.');
            return;
        }

        $ubicacion->delete();
        session()->flash('success', 'Ubicación eliminada.');
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->resetFormulario();
    }

    private function resetFormulario(): void
    {
        $this->reset(['ubicacionId', 'nombre', 'descripcion']);
        $this->resetErrorBag();
    }

    public function render()
    {
        $ubicaciones = Ubicacion::query()
            ->when($this->busqueda, fn ($q) => $q->where('nombre', 'like', "%{$this->busqueda}%"))
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.inventario.ubicaciones-catalogo', [
            'ubicaciones' => $ubicaciones,
        ]);
    }
}