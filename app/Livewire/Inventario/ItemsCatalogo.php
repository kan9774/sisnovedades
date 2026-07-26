<?php

namespace App\Livewire\Inventario;

use App\Models\Categoria;
use App\Models\Item;
use App\Models\Talla;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ItemsCatalogo extends Component
{
    use WithPagination;

    // Filtros del listado
    public string $busqueda = '';
    public ?int $filtroCategoriaId = null;

    // Estado del modal de alta/edición
    public bool $mostrarModal = false;
    public ?int $itemId = null;

    // Campos del formulario
    public string $codigo = '';
    public string $nombre = '';
    public string $descripcion = '';
    public ?int $categoria_id = null;
    public ?int $talla_id = null;
    public string $tipo_seguimiento = 'cantidad';
    public ?string $unidad_medida = null;
    public ?int $stock_minimo = null;

    protected function rules(): array
    {
        return [
            'codigo' => 'required|string|max:50|unique:items,codigo,' . $this->itemId,
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
            'categoria_id' => 'required|exists:categorias,id',
            'talla_id' => 'nullable|exists:tallas,id',
            'tipo_seguimiento' => 'required|in:cantidad,individual',
            'unidad_medida' => 'nullable|required_if:tipo_seguimiento,cantidad|string|max:30',
            'stock_minimo' => 'nullable|integer|min:0',
        ];
    }

    protected array $messages = [
        'codigo.required' => 'El código es obligatorio.',
        'codigo.unique' => 'Ya existe un ítem con ese código.',
        'nombre.required' => 'El nombre es obligatorio.',
        'categoria_id.required' => 'Seleccioná una categoría.',
        'unidad_medida.required_if' => 'Indicá la unidad de medida (unidad, caja, litro, etc).',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Item::class);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroCategoriaId(): void
    {
        $this->resetPage();
    }

    public function abrirModalCrear(): void
    {
        $this->authorize('create', Item::class);
        $this->resetFormulario();
        $this->mostrarModal = true;
        $this->dispatch('abrir-modal-item');
    }

    public function abrirModalEditar(int $itemId): void
    {
        $item = Item::findOrFail($itemId);
        $this->authorize('update', $item);

        $this->itemId = $item->id;
        $this->codigo = $item->codigo;
        $this->nombre = $item->nombre;
        $this->descripcion = (string) $item->descripcion;
        $this->categoria_id = $item->categoria_id;
        $this->talla_id = $item->talla_id;
        $this->tipo_seguimiento = $item->tipo_seguimiento;
        $this->unidad_medida = $item->unidad_medida;
        $this->stock_minimo = $item->stock_minimo;

        $this->mostrarModal = true;
        $this->dispatch('abrir-modal-item');
    }

    public function guardar(): void
    {
        $this->itemId
            ? $this->authorize('update', Item::findOrFail($this->itemId))
            : $this->authorize('create', Item::class);

        $datos = $this->validate();

        // Un item individual no usa unidad_medida ni stock_minimo (esos
        // conceptos son propios del seguimiento por cantidad).
        if ($datos['tipo_seguimiento'] === 'individual') {
            $datos['unidad_medida'] = null;
            $datos['stock_minimo'] = null;
        }

        Item::updateOrCreate(['id' => $this->itemId], $datos);

        session()->flash('success', $this->itemId ? 'Ítem actualizado.' : 'Ítem creado.');

        $this->cerrarModal();
        $this->dispatch('item-guardado');
    }

    public function eliminar(int $itemId): void
    {
        $item = Item::findOrFail($itemId);
        $this->authorize('delete', $item);

        if ($item->itemUnidades()->exists() || $item->movimientos()->exists()) {
            session()->flash('error', 'No se puede eliminar: el ítem ya tiene movimientos o unidades registradas.');
            return;
        }

        $item->delete();
        session()->flash('success', 'Ítem eliminado.');
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->resetFormulario();
        $this->dispatch('cerrar-modal-item');
    }

    private function resetFormulario(): void
    {
        $this->reset([
            'itemId', 'codigo', 'nombre', 'descripcion',
            'categoria_id', 'talla_id', 'unidad_medida', 'stock_minimo',
        ]);
        $this->tipo_seguimiento = 'cantidad';
        $this->resetErrorBag();
    }

    #[On('item-guardado')]
    public function refrescar(): void
    {
        // fuerza el re-render del listado tras guardar
    }

    public function render()
    {
        $items = Item::query()
            ->with(['categoria', 'talla'])
            ->when($this->busqueda, fn ($q) => $q->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->busqueda}%")
                    ->orWhere('codigo', 'like', "%{$this->busqueda}%");
            }))
            ->when($this->filtroCategoriaId, fn ($q) => $q->where('categoria_id', $this->filtroCategoriaId))
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.inventario.items-catalogo', [
            'items' => $items,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'tallas' => Talla::orderBy('orden')->get(),
        ]);
    }
}