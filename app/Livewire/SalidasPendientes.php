<?php

namespace App\Livewire;

use App\Models\BoletaCierre;
use App\Models\Guard;
use App\Models\SalidaVehiculo;
use Livewire\Component;
use Livewire\WithPagination;

class SalidasPendientes extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $guardia;
    public $mostrarBoleta = false;
    public $salidaPendiente = null;
    public $boleta_hora_entra = '';
    public $boleta_kms_entra = '';
    public $boleta_observaciones = '';

    public function mount($guardia)
    {
        $this->guardia = $guardia;
    }

    public function getSalidasPendientesProperty()
    {
        // Busca salidas sin retorno (sin boleta y sin hora_entra/kms_entra en la salida)
        // de guardias anteriores a la actual
        return SalidaVehiculo::whereHas('guardia', function ($query) {
            $query->where('date', '<', $this->guardia->date);
        })
            ->where(function ($query) {
                $query->whereDoesntHave('boletaCierre')
                    ->whereNull('hora_entra')
                    ->orWhereNull('kms_entra');
            })
            ->with(['vehiculo', 'conductor', 'guardia'])
            ->orderBy('guardia_id', 'desc')
            ->orderBy('hora_sale', 'desc')
            ->paginate(10);
    }

    public function abrirBoleta(int $salidaId)
    {
        $this->salidaPendiente = SalidaVehiculo::with(['vehiculo', 'conductor', 'guardia'])->findOrFail($salidaId);
        $this->authorize('update', $this->salidaPendiente);
        abort_unless($this->guardia->status === 'open', 403);

        $this->mostrarBoleta = true;
        $this->boleta_hora_entra = '';
        $this->boleta_kms_entra = '';
        $this->boleta_observaciones = '';
    }

    public function cerrarBoleta()
    {
        $this->mostrarBoleta = false;
        $this->salidaPendiente = null;
    }

    public function guardarBoleta()
    {
        $this->authorize('update', $this->salidaPendiente);
        abort_unless($this->guardia->status === 'open', 403);

        $this->validate([
            'boleta_hora_entra' => 'required|date_format:H:i',
            'boleta_kms_entra' => 'required|integer|min:0|gte:salidaPendiente.kms_sale',
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