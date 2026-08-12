<?php

namespace App\Livewire\Inventario;

use App\Models\Categoria;
use App\Models\Item;
use App\Models\Talla;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ItemsCatalogo extends Component
{
    use WithPagination, WithFileUploads;


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
    public ?int $vida_util_meses = null;

    public $archivoExcel;
    public array $erroresImportacion = [];

    /**
     * true mientras el código en pantalla sigue siendo el sugerido
     * automáticamente (o está vacío). Se apaga en cuanto el usuario
     * lo toca a mano, y a partir de ahí cambiar de categoría deja de
     * pisarlo. Ver updatedCodigo() / updatedCategoriaId().
     */
    public bool $codigoAuto = true;

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
            'vida_util_meses' => 'nullable|integer|min:1|max:600',
        ];
    }

    protected array $messages = [
        'codigo.required' => 'El código es obligatorio.',
        'codigo.unique' => 'Ya existe un ítem con ese código.',
        'nombre.required' => 'El nombre es obligatorio.',
        'categoria_id.required' => 'Seleccioná una categoría.',
        'unidad_medida.required_if' => 'Indicá la unidad de medida (unidad, caja, litro, etc).',
        'vida_util_meses.integer' => 'La vida útil debe ser un número entero de meses.',
        'vida_util_meses.min' => 'La vida útil debe ser de al menos 1 mes.',
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

    /*
    |--------------------------------------------------------------------
    | Sugerencia automática de código
    |--------------------------------------------------------------------
    */

    /**
     * Se dispara cuando el usuario ELIGE una categoría en el <select>
     * (necesita wire:model.live="categoria_id" en la vista). Si el
     * código todavía no fue tocado a mano, se recalcula la sugerencia.
     */
    public function updatedCategoriaId(): void
    {
        if ($this->codigoAuto && $this->categoria_id) {
            $sugerido = $this->generarCodigoSugerido($this->categoria_id);

            if ($sugerido !== null) {
                $this->codigo = $sugerido;
            }
        }
    }

    /**
     * Se dispara solo cuando el propio campo Código cambia por una
     * edición real del usuario (necesita wire:model.live en la vista;
     * los cambios que este componente hace por su cuenta, como en
     * updatedCategoriaId(), NO vuelven a disparar este hook). A partir
     * de acá, cambiar de categoría ya no pisa lo que el usuario escribió.
     */
    public function updatedCodigo(): void
    {
        $this->codigoAuto = false;
    }

    /**
     * Prefijo de la categoría + correlativo con 4 dígitos, scoped por
     * categoría (cada categoría arranca su propio conteo en 0001).
     * Devuelve null si la categoría no tiene abreviatura cargada (no
     * hay de dónde sacar el prefijo, así que no se sugiere nada y el
     * usuario carga el código a mano para ese caso).
     */
    private function generarCodigoSugerido(int $categoriaId): ?string
    {
        $categoria = Categoria::find($categoriaId);

        if (! $categoria || empty($categoria->codigo_abreviatura)) {
            return null;
        }

        $prefijo = strtoupper($categoria->codigo_abreviatura);

        $ultimoNumero = Item::where('categoria_id', $categoriaId)
            ->where('codigo', 'like', $prefijo . '-%')
            ->get()
            ->map(fn(Item $item) => (int) substr($item->codigo, strlen($prefijo) + 1))
            ->max() ?? 0;

        return $prefijo . '-' . str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);
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
        $this->vida_util_meses = $item->vida_util_meses;

        // Al editar, el código ya es real: que cambiar de categoría acá
        // NO lo pise (si el admin corrige la categoría de un ítem viejo,
        // no queremos que le regeneremos el código existente solo).
        $this->codigoAuto = false;

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
            'itemId',
            'codigo',
            'nombre',
            'descripcion',
            'categoria_id',
            'talla_id',
            'unidad_medida',
            'stock_minimo',
            'vida_util_meses',
        ]);
        $this->tipo_seguimiento = 'cantidad';
        $this->codigoAuto = true;
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
            ->when($this->busqueda, fn($q) => $q->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->busqueda}%")
                    ->orWhere('codigo', 'like', "%{$this->busqueda}%");
            }))
            ->when($this->filtroCategoriaId, fn($q) => $q->where('categoria_id', $this->filtroCategoriaId))
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.inventario.items-catalogo', [
            'items' => $items,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'tallas' => Talla::orderBy('orden')->get(),
        ]);
    }
    public function abrirModalImportar()
    {
        $this->reset('archivoExcel', 'erroresImportacion');
        $this->dispatch('abrir-modal-importar');
    }

    public function importar()
    {
        $this->validate([
            'archivoExcel' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $import = new \App\Imports\ItemsImport();
        $import->importar($this->archivoExcel->getRealPath());

        $this->erroresImportacion = $import->errores;

        if ($import->filasImportadas > 0) {
            session()->flash('success', "{$import->filasImportadas} ítems importados correctamente.");
        }

        if (empty($this->erroresImportacion)) {
            $this->dispatch('cerrar-modal-importar');
        }

        $this->reset('archivoExcel');
    }
}
