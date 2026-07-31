<?php

namespace App\Livewire\Inventario;

use App\Models\ItemUnidad;
use App\Models\Movimiento;
use App\Models\Oficina;
use App\Models\Stock;
use App\Models\Ubicacion;
use App\Models\User;
use App\Models\Vehiculo;
use Livewire\Component;
use Livewire\WithPagination;

class UbicacionesCatalogo extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public string $filtroTipo = '';

    // Fila de alta
    public string $nombre = '';
    public string $tipo = 'deposito';
    public ?int $referencia_id = null;

    // Fila en modo edición
    public ?int $editingId = null;
    public string $editNombre = '';
    public string $editTipo = 'deposito';
    public ?int $editReferenciaId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Ubicacion::class);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------
    | Alta (fila superior)
    |--------------------------------------------------------------------
    */

    public function updatingTipo(): void
    {
        $this->reset(['referencia_id']);
        $this->resetErrorBag('referencia_id');
    }
    public function paginationView(): string
    {
        return 'livewire::bootstrap';
    }

    public function updatedReferenciaId(): void
    {
        if (! $this->referencia_id) {
            return;
        }

        $this->nombre = $this->nombreDesdeReferencia($this->tipo, $this->referencia_id) ?? $this->nombre;
    }

    public function agregar(): void
    {
        $this->authorize('create', Ubicacion::class);

        $reglas = [
            'nombre' => 'required|string|max:150|unique:ubicaciones,nombre',
            'tipo' => 'required|in:deposito,oficina,vehiculo,persona',
        ];
        $reglas['referencia_id'] = $this->tipo === 'deposito'
            ? 'nullable'
            : 'required|integer|exists:' . $this->tablaReferencia($this->tipo) . ',id';

        $datos = $this->validate($reglas, [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe una ubicación con ese nombre.',
            'referencia_id.required' => 'Seleccioná a qué registro corresponde esta ubicación.',
            'referencia_id.exists' => 'El registro seleccionado no es válido.',
        ]);

        if ($datos['tipo'] === 'deposito') {
            $datos['referencia_id'] = null;
        }

        Ubicacion::create($datos);

        session()->flash('success', 'Ubicación creada.');

        $this->reset(['nombre', 'referencia_id']);
        $this->tipo = 'deposito';
        $this->resetErrorBag();
    }

    /*
    |--------------------------------------------------------------------
    | Edición inline (fila de la tabla)
    |--------------------------------------------------------------------
    */

    public function updatingEditTipo(): void
    {
        $this->reset(['editReferenciaId']);
        $this->resetErrorBag('editReferenciaId');
    }

    public function updatedEditReferenciaId(): void
    {
        if (! $this->editReferenciaId) {
            return;
        }

        $this->editNombre = $this->nombreDesdeReferencia($this->editTipo, $this->editReferenciaId) ?? $this->editNombre;
    }

    public function startEdit(int $ubicacionId): void
    {
        $ubicacion = Ubicacion::findOrFail($ubicacionId);
        $this->authorize('update', $ubicacion);

        $this->editingId = $ubicacion->id;
        $this->editNombre = $ubicacion->nombre;
        $this->editTipo = $ubicacion->tipo;
        $this->editReferenciaId = $ubicacion->referencia_id;
        $this->resetErrorBag();
    }

    public function saveEdit(): void
    {
        $ubicacion = Ubicacion::findOrFail($this->editingId);
        $this->authorize('update', $ubicacion);

        $reglas = [
            'editNombre' => 'required|string|max:150|unique:ubicaciones,nombre,' . $this->editingId,
            'editTipo' => 'required|in:deposito,oficina,vehiculo,persona',
        ];
        $reglas['editReferenciaId'] = $this->editTipo === 'deposito'
            ? 'nullable'
            : 'required|integer|exists:' . $this->tablaReferencia($this->editTipo) . ',id';

        $datos = $this->validate($reglas, [
            'editNombre.required' => 'El nombre es obligatorio.',
            'editNombre.unique' => 'Ya existe una ubicación con ese nombre.',
            'editReferenciaId.required' => 'Seleccioná a qué registro corresponde esta ubicación.',
            'editReferenciaId.exists' => 'El registro seleccionado no es válido.',
        ]);

        $ubicacion->update([
            'nombre' => $datos['editNombre'],
            'tipo' => $datos['editTipo'],
            'referencia_id' => $datos['editTipo'] === 'deposito' ? null : $datos['editReferenciaId'],
        ]);

        session()->flash('success', 'Ubicación actualizada.');

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editNombre', 'editReferenciaId']);
        $this->editTipo = 'deposito';
        $this->resetErrorBag();
    }

    /*
    |--------------------------------------------------------------------
    | Baja
    |--------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------
    */

    /**
     * Lista de opciones (oficinas/vehículos/personas) para el <select> de
     * referencia. No usa #[Computed] porque necesita recibir el tipo como
     * parámetro (uno para la fila de alta, otro para la fila en edición).
     */
    public function opcionesReferencia(string $tipo)
    {
        return match ($tipo) {
            'oficina' => Oficina::orderBy('nombre')->get(['id', 'nombre']),
            'vehiculo' => Vehiculo::orderBy('matricula')->get(),
            'persona' => User::orderBy('name')->with('grado')->get(['id', 'name', 'last_name', 'grado_id', 'email']),
            default => collect(),
        };
    }

    /**
     * Nombre para mostrar de una persona: "Grado Nombre Apellido".
     */
    public function formatearPersona(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return trim(collect([$user->grade, $user->name, $user->last_name])->filter()->implode(' '));
    }

    private function nombreDesdeReferencia(string $tipo, int $referenciaId): ?string
    {
        return match ($tipo) {
            'oficina' => Oficina::find($referenciaId)?->nombre,
            'vehiculo' => Vehiculo::find($referenciaId)?->nombre_completo,
            'persona' => $this->formatearPersona(User::find($referenciaId)),
            default => null,
        };
    }

    private function tablaReferencia(string $tipo): string
    {
        return match ($tipo) {
            'oficina' => 'oficinas',
            'vehiculo' => 'vehiculos',
            'persona' => 'users',
            default => 'ubicaciones', // no debería usarse (deposito no valida referencia)
        };
    }

    public function render()
    {
        $ubicaciones = Ubicacion::query()
            ->when($this->busqueda, fn($q) => $q->where('nombre', 'like', "%{$this->busqueda}%"))
            ->when($this->filtroTipo, fn($q) => $q->where('tipo', $this->filtroTipo))
            ->orderBy('nombre')
            ->paginate(15);

        return view('livewire.inventario.ubicaciones-catalogo', [
            'ubicaciones' => $ubicaciones,
        ]);
    }
}
