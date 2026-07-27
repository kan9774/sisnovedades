<?php

namespace App\Livewire\Inventario;

use App\Models\Entrega;
use App\Models\Item;
use App\Models\ItemUnidad;
use App\Models\Stock;
use App\Models\Ubicacion;
use App\Services\InventarioService;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Livewire\Component;

class EntregasInventario extends Component
{
    // 'entrega' (depósito -> persona/oficina/vehículo) o
    // 'devolucion' (persona/oficina/vehículo -> depósito)
    public string $tipo = 'entrega';

    public ?int $origenId = null;
    public ?int $destinoId = null;
    public ?string $motivo = null;

    // Formulario para agregar una línea al carrito
    public ?int $lineaItemId = null;
    public ?int $lineaCantidad = null;
    public ?int $lineaItemUnidadId = null;

    // El carrito: array de líneas
    // ['item_id', 'item_nombre', 'tipo_seguimiento', 'cantidad', 'item_unidad_id', 'numero_serie']
    public array $lineas = [];

    public function mount(): void
    {
        $this->authorize('viewAny', Entrega::class);
    }

    public function updatingTipo(): void
    {
        $this->reset(['origenId', 'destinoId', 'lineas', 'lineaItemId', 'lineaCantidad', 'lineaItemUnidadId']);
    }

    public function updatingOrigenId(): void
    {
        $this->reset(['lineas', 'lineaItemId', 'lineaCantidad', 'lineaItemUnidadId']);
    }

    public function updatingLineaItemId(): void
    {
        $this->reset(['lineaCantidad', 'lineaItemUnidadId']);
    }

    /**
     * Unidades individuales candidatas para la línea actual: las del
     * ítem elegido que estén disponibles en el origen seleccionado.
     */
    public function getUnidadesCandidatasProperty()
    {
        if (! $this->origenId || ! $this->lineaItemId) {
            return collect();
        }

        $yaEnCarrito = collect($this->lineas)->pluck('item_unidad_id')->filter()->all();

        return ItemUnidad::where('item_id', $this->lineaItemId)
            ->where('ubicacion_actual_id', $this->origenId)
            ->whereIn('estado', ['disponible', 'asignado'])
            ->whereNotIn('id', $yaEnCarrito)
            ->orderBy('numero_serie')
            ->get();
    }

    public function agregarLinea(): void
    {
        $this->resetErrorBag();

        if (! $this->lineaItemId) {
            $this->addError('lineaItemId', 'Seleccioná un ítem.');
            return;
        }

        $item = Item::findOrFail($this->lineaItemId);

        if ($item->esPorCantidad()) {
            if (! $this->lineaCantidad || $this->lineaCantidad <= 0) {
                $this->addError('lineaCantidad', 'Indicá una cantidad mayor a cero.');
                return;
            }

            $disponible = $this->stockDisponibleEnOrigen($item);
            if ($this->lineaCantidad > $disponible) {
                $this->addError('lineaCantidad', "Solo hay {$disponible} disponibles en el origen.");
                return;
            }

            // Si el ítem ya está en el carrito, sumamos en vez de duplicar.
            foreach ($this->lineas as $i => $linea) {
                if (($linea['item_id'] ?? null) === $item->id && ! $linea['item_unidad_id']) {
                    $this->lineas[$i]['cantidad'] += $this->lineaCantidad;
                    $this->limpiarFormularioLinea();
                    return;
                }
            }

            $this->lineas[] = [
                'item_id' => $item->id,
                'item_nombre' => $item->nombre,
                'tipo_seguimiento' => 'cantidad',
                'cantidad' => $this->lineaCantidad,
                'item_unidad_id' => null,
                'numero_serie' => null,
            ];
        } else {
            if (! $this->lineaItemUnidadId) {
                $this->addError('lineaItemUnidadId', 'Seleccioná qué unidad puntual.');
                return;
            }

            $unidad = ItemUnidad::findOrFail($this->lineaItemUnidadId);

            $this->lineas[] = [
                'item_id' => $item->id,
                'item_nombre' => $item->nombre,
                'tipo_seguimiento' => 'individual',
                'cantidad' => null,
                'item_unidad_id' => $unidad->id,
                'numero_serie' => $unidad->numero_serie ?? "unidad #{$unidad->id}",
            ];
        }

        $this->limpiarFormularioLinea();
    }

    public function quitarLinea(int $indice): void
    {
        unset($this->lineas[$indice]);
        $this->lineas = array_values($this->lineas);
    }

    /**
     * Carga automáticamente todo lo que hay actualmente en el origen
     * (stock por cantidad + unidades individuales), pensado para el
     * caso de baja total (ej: alguien deja de prestar servicio y
     * devuelve todo lo que tenía asignado).
     */
    public function cargarTodoElOrigen(): void
    {
        if (! $this->origenId) {
            $this->addError('origenId', 'Elegí primero el origen.');
            return;
        }

        $this->lineas = [];

        Stock::where('ubicacion_id', $this->origenId)
            ->where('cantidad', '>', 0)
            ->with('item')
            ->get()
            ->each(function (Stock $stock) {
                $this->lineas[] = [
                    'item_id' => $stock->item_id,
                    'item_nombre' => $stock->item->nombre,
                    'tipo_seguimiento' => 'cantidad',
                    'cantidad' => $stock->cantidad,
                    'item_unidad_id' => null,
                    'numero_serie' => null,
                ];
            });

        ItemUnidad::where('ubicacion_actual_id', $this->origenId)
            ->whereIn('estado', ['disponible', 'asignado'])
            ->with('item')
            ->get()
            ->each(function (ItemUnidad $unidad) {
                $this->lineas[] = [
                    'item_id' => $unidad->item_id,
                    'item_nombre' => $unidad->item->nombre,
                    'tipo_seguimiento' => 'individual',
                    'cantidad' => null,
                    'item_unidad_id' => $unidad->id,
                    'numero_serie' => $unidad->numero_serie ?? "unidad #{$unidad->id}",
                ];
            });

        if (empty($this->lineas)) {
            session()->flash('error', 'No hay stock ni unidades asignadas en esa ubicación para cargar.');
        }
    }

    public function confirmar()
    {
        $this->authorize('create', Entrega::class);

        $this->validate([
            'origenId' => 'required|exists:ubicaciones,id|different:destinoId',
            'destinoId' => 'required|exists:ubicaciones,id',
            'motivo' => 'nullable|string|max:255',
        ], [
            'origenId.required' => 'Elegí el origen.',
            'origenId.different' => 'El origen y el destino no pueden ser el mismo.',
            'destinoId.required' => 'Elegí el destino.',
        ]);

        if (empty($this->lineas)) {
            session()->flash('error', 'Agregá al menos un ítem antes de confirmar.');
            return;
        }

        try {
            $entrega = app(InventarioService::class)->registrarEntregaCarrito(
                $this->tipo,
                Ubicacion::findOrFail($this->origenId),
                Ubicacion::findOrFail($this->destinoId),
                Auth::user(),
                $this->lineas,
                $this->motivo,
            );
        } catch (InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        return redirect()->route('admin.inventario.entregas.comprobante', $entrega);
    }

    private function limpiarFormularioLinea(): void
    {
        $this->reset(['lineaItemId', 'lineaCantidad', 'lineaItemUnidadId']);
        $this->resetErrorBag();
    }

    private function stockDisponibleEnOrigen(Item $item): int
    {
        if (! $this->origenId) {
            return 0;
        }

        return Stock::where('item_id', $item->id)
            ->where('ubicacion_id', $this->origenId)
            ->value('cantidad') ?? 0;
    }

    public function render()
    {
        // El origen depende del tipo de operación:
        // entrega sale de un depósito, devolución sale de persona/oficina/vehículo.
        $origenes = $this->tipo === 'entrega'
            ? Ubicacion::where('tipo', 'deposito')->orderBy('nombre')->get()
            : Ubicacion::whereIn('tipo', ['persona', 'oficina', 'vehiculo'])->orderBy('nombre')->get();

        $destinos = $this->tipo === 'entrega'
            ? Ubicacion::whereIn('tipo', ['persona', 'oficina', 'vehiculo'])->orderBy('nombre')->get()
            : Ubicacion::where('tipo', 'deposito')->orderBy('nombre')->get();

        return view('livewire.inventario.entregas-inventario', [
            'origenes' => $origenes,
            'destinos' => $destinos,
            'items' => Item::orderBy('nombre')->get(),
            'unidadesCandidatas' => $this->unidadesCandidatas,
        ]);
    }
}