<?php

namespace App\Livewire\Inventario;

use App\Exceptions\StockInsuficienteException;
use App\Models\Item;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Services\InventarioService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MovimientosInventario extends Component
{
    use WithPagination;

    // Formulario de registro
    public string $tipo = 'entrada'; // entrada | salida | transferencia | ajuste
    public ?int $item_id = null;
    public ?int $ubicacion_origen_id = null;
    public ?int $ubicacion_destino_id = null;
    public ?int $cantidad = null;
    public ?string $motivo = null;
    public ?string $referencia = null;

    // Filtros del historial
    public string $filtroTipo = '';
    public ?int $filtroItemId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Movimiento::class);
    }

    protected function rules(): array
    {
        $reglas = [
            'tipo' => 'required|in:entrada,salida,transferencia,ajuste',
            'item_id' => 'required|exists:items,id',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255',
            'referencia' => 'nullable|string|max:100',
        ];

        if (in_array($this->tipo, ['salida', 'transferencia'])) {
            $reglas['ubicacion_origen_id'] = 'required|exists:ubicaciones,id';
        }

        if (in_array($this->tipo, ['entrada', 'transferencia'])) {
            $reglas['ubicacion_destino_id'] = 'required|exists:ubicaciones,id|different:ubicacion_origen_id';
        }

        if ($this->tipo === 'ajuste') {
            // en ajuste, "cantidad" representa la cantidad REAL contada, no un delta
            $reglas['ubicacion_destino_id'] = 'required|exists:ubicaciones,id';
            $reglas['cantidad'] = 'required|integer|min:0';
        }

        return $reglas;
    }

    protected array $messages = [
        'item_id.required' => 'Seleccioná un ítem.',
        'cantidad.required' => 'Indicá la cantidad.',
        'cantidad.min' => 'La cantidad debe ser mayor a cero.',
        'ubicacion_origen_id.required' => 'Seleccioná la ubicación de origen.',
        'ubicacion_destino_id.required' => 'Seleccioná la ubicación de destino.',
        'ubicacion_destino_id.different' => 'El origen y el destino no pueden ser la misma ubicación.',
    ];

    public function updatingTipo(): void
    {
        $this->reset(['ubicacion_origen_id', 'ubicacion_destino_id', 'cantidad']);
        $this->resetErrorBag();
    }

    #[Computed]
    public function itemSeleccionado(): ?Item
    {
        return $this->item_id ? Item::find($this->item_id) : null;
    }

    /**
     * Stock actual del ítem seleccionado en la ubicación relevante
     * (origen para salida/transferencia, destino para ajuste), solo
     * para mostrarlo como referencia mientras se completa el formulario.
     */
    #[Computed]
    public function stockDeReferencia(): ?int
    {
        $item = $this->itemSeleccionado;

        if (! $item || ! $item->esPorCantidad()) {
            return null;
        }

        $ubicacionId = in_array($this->tipo, ['salida', 'transferencia'])
            ? $this->ubicacion_origen_id
            : $this->ubicacion_destino_id;

        if (! $ubicacionId) {
            return null;
        }

        return app(InventarioService::class)->stockActual($item, Ubicacion::find($ubicacionId));
    }

    public function registrar(): void
    {
        $item = Item::findOrFail($this->validate()['item_id']);

        if (! $item->esPorCantidad()) {
            $this->addError('item_id', 'Este ítem es de seguimiento individual; los movimientos por cantidad no aplican.');
            return;
        }

        $this->authorizeAccionSegunTipo();

        $servicio = app(InventarioService::class);
        $usuario = Auth::user();

        try {
            match ($this->tipo) {
                'entrada' => $servicio->registrarEntrada(
                    $item,
                    Ubicacion::findOrFail($this->ubicacion_destino_id),
                    $this->cantidad,
                    $usuario,
                    $this->motivo,
                    $this->referencia,
                ),
                'salida' => $servicio->registrarSalida(
                    $item,
                    Ubicacion::findOrFail($this->ubicacion_origen_id),
                    $this->cantidad,
                    $usuario,
                    $this->motivo,
                    $this->referencia,
                ),
                'transferencia' => $servicio->registrarTransferencia(
                    $item,
                    Ubicacion::findOrFail($this->ubicacion_origen_id),
                    Ubicacion::findOrFail($this->ubicacion_destino_id),
                    $this->cantidad,
                    $usuario,
                    $this->motivo,
                ),
                'ajuste' => $servicio->registrarAjuste(
                    $item,
                    Ubicacion::findOrFail($this->ubicacion_destino_id),
                    $this->cantidad,
                    $usuario,
                    $this->motivo,
                ),
            };
        } catch (StockInsuficienteException $e) {
            $this->addError('cantidad', $e->getMessage());
            return;
        } catch (InvalidArgumentException $e) {
            $this->addError('cantidad', $e->getMessage());
            return;
        }

        session()->flash('success', 'Movimiento registrado correctamente.');
        $this->reset(['item_id', 'ubicacion_origen_id', 'ubicacion_destino_id', 'cantidad', 'motivo', 'referencia']);
        unset($this->itemSeleccionado, $this->stockDeReferencia);
    }

    private function authorizeAccionSegunTipo(): void
    {
        $habilidad = match ($this->tipo) {
            'entrada' => 'registrarEntrada',
            'salida' => 'registrarSalida',
            'transferencia' => 'registrarTransferencia',
            'ajuste' => 'registrarAjuste',
        };

        if (Gate::denies($habilidad, Movimiento::class)) {
            abort(403);
        }
    }

    public function render()
    {
        $movimientos = Movimiento::query()
            ->with(['item', 'itemUnidad', 'ubicacionOrigen', 'ubicacionDestino', 'usuario'])
            ->when($this->filtroTipo, fn ($q) => $q->where('tipo', $this->filtroTipo))
            ->when($this->filtroItemId, fn ($q) => $q->where('item_id', $this->filtroItemId))
            ->latest()
            ->paginate(15);

        return view('livewire.inventario.movimientos-inventario', [
            'movimientos' => $movimientos,
            'items' => Item::where('tipo_seguimiento', 'cantidad')->orderBy('nombre')->get(),
            'ubicaciones' => Ubicacion::orderBy('nombre')->get(),
        ]);
    }
}