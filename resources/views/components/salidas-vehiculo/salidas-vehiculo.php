<?php

use App\Models\Conductor;
use App\Models\Guard;
use App\Models\SalidaVehiculo;
use App\Models\Vehiculo;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public Guard $guardia;
    public bool $puedeOperarGuardia = false;

    public ?int $editandoId = null;

    public string $vehiculo_id = '';
    public string $conductor_id = '';
    public string $tipo_combustible = '';
    public string $hora_sale = '';
    public string $hora_entra = '';
    public string $kms_sale = '';
    public string $kms_entra = '';
    public string $comision = '';

    public function mount(Guard $guardia, bool $puedeOperarGuardia = false): void
    {
        $this->guardia = $guardia;
        $this->puedeOperarGuardia = $puedeOperarGuardia;
    }

    #[Computed]
    public function vehiculos()
    {
        return Vehiculo::where('activo', true)->orderBy('matricula')->get();
    }

    #[Computed]
    public function conductores()
    {
        return Conductor::where('activo', true)->orderBy('primer_apellido')->get();
    }

    #[Computed]
    public function salidas()
    {
        return $this->guardia->salidasVehiculos()
            ->with(['vehiculo', 'conductor'])
            ->orderBy('hora_sale')
            ->paginate(10);
    }

    #[Computed]
    public function resumenCombustible()
    {
        return $this->guardia->salidasVehiculos()
            ->selectRaw('tipo_combustible, SUM(kms_recorridos) as total_kms, SUM(litros) as total_litros')
            ->groupBy('tipo_combustible')
            ->get();
    }

    public function abrirCrear(): void
    {
        $this->resetValidation();
        $this->reset(['editandoId', 'vehiculo_id', 'conductor_id', 'tipo_combustible', 'hora_sale', 'hora_entra', 'kms_sale', 'kms_entra', 'comision']);
        $this->dispatch('abrir-modal-salida');
    }

    public function abrirEditar(int $id): void
    {
        $salida = $this->guardia->salidasVehiculos()->findOrFail($id);
        $this->authorize('update', $salida);

        $this->resetValidation();
        $this->editandoId = $salida->id;
        $this->vehiculo_id = (string) $salida->vehiculo_id;
        $this->conductor_id = (string) $salida->conductor_id;
        $this->tipo_combustible = $salida->tipo_combustible;
        $this->hora_sale = $salida->hora_sale?->format('H:i') ?? '';
        $this->hora_entra = $salida->hora_entra?->format('H:i') ?? '';
        $this->kms_sale = (string) ($salida->kms_sale ?? '');
        $this->kms_entra = (string) ($salida->kms_entra ?? '');
        $this->comision = $salida->comision;

        $this->dispatch('abrir-modal-salida');
    }

    public function guardar(): void
    {
        abort_unless($this->puedeOperarGuardia && $this->guardia->status === 'open', 403);

        $rules = [
            'vehiculo_id'      => 'required|exists:vehiculos,id',
            'conductor_id'     => 'required|exists:conductores,id',
            'tipo_combustible' => 'required|in:gas_oil,nafta',
            'hora_sale'        => 'required|date_format:H:i',
            'hora_entra'       => 'nullable|date_format:H:i|after:hora_sale',
            'kms_sale'         => 'nullable|integer|min:0',
            'kms_entra'        => 'nullable|integer|min:0|gt:kms_sale',
            'comision'         => 'required|string',
        ];

        $vehiculo = Vehiculo::find($this->vehiculo_id);
        if ($vehiculo && !$vehiculo->sin_cuentakilometros) {
            $rules['kms_sale'] = 'required|integer|min:0';
            $rules['kms_entra'] = 'required|integer|min:0|gt:kms_sale';
        }

        $data = $this->validate($rules);
        $data['kms_sale'] = $data['kms_sale'] !== '' ? $data['kms_sale'] : null;
        $data['kms_entra'] = $data['kms_entra'] !== '' ? $data['kms_entra'] : null;

        if ($this->editandoId) {
            $salida = $this->guardia->salidasVehiculos()->findOrFail($this->editandoId);
            $this->authorize('update', $salida);
            $salida->update($data);
        } else {
            $this->authorize('create', SalidaVehiculo::class);
            $data['guardia_id'] = $this->guardia->id;
            SalidaVehiculo::create($data);
        }

        unset($this->salidas, $this->resumenCombustible);
        $this->dispatch('cerrar-modal-salida');
    }

    public function eliminar(int $id): void
    {
        $salida = $this->guardia->salidasVehiculos()->findOrFail($id);
        $this->authorize('delete', $salida);

        $salida->delete();
        unset($this->salidas, $this->resumenCombustible);
    }
};