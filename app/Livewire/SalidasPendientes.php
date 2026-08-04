<?php

namespace App\Livewire;

use App\Models\BoletaCierre;
use App\Models\Guard;
use App\Models\SalidaVehiculo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class SalidasPendientes extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $guardia;
    public $mostrarBoleta = false;

    #[Locked]
    public ?int $salidaPendienteId = null;

    public $boleta_hora_entra = '';
    public $boleta_kms_entra = '';
    public $boleta_observaciones = '';

    public function mount($guardia)
    {
        $this->guardia = $guardia;
    }

    #[Computed]
    public function salidaPendiente(): ?SalidaVehiculo
    {
        if (! $this->salidaPendienteId) {
            return null;
        }

        // find() en vez de findOrFail(): si la salida fue borrada entre
        // que se abrió el panel y se guarda la boleta, no debe romper
        // la re-hidratación del componente.
        return SalidaVehiculo::with(['vehiculo', 'conductor', 'guardia','tipoCombustible'])
            ->find($this->salidaPendienteId);
    }

    #[Computed]
    public function salidasPendientes()
    {
        // Busca salidas sin retorno (sin boleta y sin hora_entra/kms_entra en la salida)
        // de guardias anteriores a la actual
        return SalidaVehiculo::whereHas('guardia', function ($query) {
            $query->where('date', '<', $this->guardia->date);
        })
            ->where(function ($query) {
                $query->whereDoesntHave('boletaCierre')
                    ->where(function ($q) {
                        $q->whereNull('hora_entra')
                            ->orWhere(function ($q2) {
                                $q2->whereNull('kms_entra')
                                    ->whereHas('vehiculo', function ($vq) {
                                        $vq->where('sin_cuentakilometros', false);
                                    });
                            });
                    });
            })
            ->with(['vehiculo', 'conductor', 'guardia', 'tipoCombustible'])
            ->orderBy('guardia_id', 'desc')
            ->orderBy('hora_sale', 'desc')
            ->paginate(10);
    }

    public function abrirBoleta(int $salidaId)
    {
        $salida = SalidaVehiculo::findOrFail($salidaId);
        $this->authorize('update', $salida);
        abort_unless($this->guardia->status === 'open', 403);

        $this->salidaPendienteId = $salidaId;
        unset($this->salidaPendiente);

        $this->mostrarBoleta = true;
        $this->boleta_hora_entra = '';
        $this->boleta_kms_entra = '';
        $this->boleta_observaciones = '';
    }

    public function cerrarBoleta()
    {
        $this->mostrarBoleta = false;
        $this->salidaPendienteId = null;
        unset($this->salidaPendiente);
    }

    public function guardarBoleta()
    {
        if (! $this->salidaPendiente) {
            $this->cerrarBoleta();
            session()->flash('error', 'Esta salida ya no existe (pudo haber sido eliminada). Actualizá la lista.');
            return;
        }

        $this->authorize('update', $this->salidaPendiente);
        abort_unless($this->guardia->status === 'open', 403);

        $sinCuentakilometros = $this->salidaPendiente->vehiculo?->sin_cuentakilometros;

        $this->validate([
            'boleta_hora_entra' => 'required|date_format:H:i',
            'boleta_kms_entra' => $sinCuentakilometros
                ? 'nullable|integer|min:0'
                : 'required|integer|min:0|gte:salidaPendiente.kms_sale',
            'boleta_observaciones' => 'nullable|string|max:500',
        ]);

        // Crear la boleta de cierre vinculada a la guardia actual
        BoletaCierre::updateOrCreate(
            ['salida_id' => $this->salidaPendiente->id],
            [
                'guardia_id' => $this->guardia->id,
                'fecha_entra' => now()->toDateString(),
                'hora_entra' => $this->boleta_hora_entra,
                'kms_entra' => $this->boleta_kms_entra,
                'observaciones' => $this->boleta_observaciones,
            ]
        );

        $this->cerrarBoleta();
        $this->dispatch('salida-actualizada');
        session()->flash('success', 'Boleta de cierre registrada correctamente.');
    }

    public function render()
    {
        return view('livewire.salidas-pendientes');
    }
}
