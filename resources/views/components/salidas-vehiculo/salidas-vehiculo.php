<?php

use App\Models\BoletaCierre;
use App\Models\Conductor;
use App\Models\Guard;
use App\Models\SalidaVehiculo;
use App\Models\Vehiculo;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public Guard $guardia;
    public bool $puedeOperarGuardia = false;

    public ?int $editandoId = null;
    public bool $showModal = false;

    // Boleta de cierre
    public ?int $boletaSalidaId = null;
    public bool $showBoletaModal = false;
    public string $boleta_fecha_entra = '';
    public string $boleta_hora_entra = '';
    public string $boleta_kms_entra = '';
    public string $boleta_observaciones = '';
    public ?SalidaVehiculo $salida = null;

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

    public function getVehiculosProperty()
    {
        return Vehiculo::where('activo', true)->orderBy('matricula')->get();
    }

    public function getConductoresProperty()
    {
        return Conductor::where('activo', true)->orderBy('primer_apellido')->get();
    }

    /**
     * Cache en memoria (por request) de la colección combinada, para que
     * getSalidasProperty() y getResumenCombustibleProperty() no disparen
     * las mismas queries dos veces cada una.
     */
    protected ?\Illuminate\Support\Collection $todasSalidasCache = null;

    /**
     * Colección combinada: salidas que se originaron en esta guardia
     * + salidas de OTRA guardia cuya boleta de cierre (regreso) se
     * registró en esta guardia.
     */
    protected function todasSalidasCollection()
    {
        if ($this->todasSalidasCache !== null) {
            return $this->todasSalidasCache;
        }

        $misSalidas = $this->guardia->salidasVehiculos()
            ->with(['vehiculo', 'conductor', 'boletaCierre', 'guardia'])
            ->get();

        $retornos = SalidaVehiculo::whereHas('boletaCierre', function ($q) {
                $q->where('guardia_id', $this->guardia->id);
            })
            ->where('guardia_id', '!=', $this->guardia->id)
            ->with(['vehiculo', 'conductor', 'boletaCierre', 'guardia'])
            ->get();

        return $this->todasSalidasCache = $misSalidas->concat($retornos)->sortBy('hora_sale')->values();
    }

    public function getSalidasProperty()
    {
        $todas = $this->todasSalidasCollection();
        $perPage = 10;
        $page = $this->getPage();

        $items = $todas->forPage($page, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $todas->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    public function getResumenCombustibleProperty()
    {
        return $this->todasSalidasCollection()
            ->groupBy('tipo_combustible')
            ->map(fn ($grupo, $tipo) => (object) [
                'tipo_combustible' => $tipo,
                'total_kms' => $grupo->sum('kms_recorridos'),
                'total_litros' => $grupo->sum('litros'),
            ])
            ->values();
    }

    public function refreshSalidas(): void
    {
        // Solo un placeholder para el wire:poll
    }

    public function abrirCrear(): void
    {
        $this->resetValidation();
        $this->reset(['editandoId', 'vehiculo_id', 'conductor_id', 'tipo_combustible', 'hora_sale', 'hora_entra', 'kms_sale', 'kms_entra', 'comision']);
        $this->showModal = true;
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

        $this->showModal = true;
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->editandoId = null;
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
            'kms_entra'        => 'nullable|integer|min:0|gte:kms_sale',
            'comision'         => 'required|string',
        ];

        $vehiculo = Vehiculo::find($this->vehiculo_id);
        if ($vehiculo && !$vehiculo->sin_cuentakilometros) {
            $rules['kms_sale'] = 'required|integer|min:0';
            // kms_entra es opcional: el vehículo puede no haber vuelto aún
            // Pero si se ingresa, debe ser mayor o igual al km de salida
            $rules['kms_entra'] = 'nullable|integer|min:0|gte:kms_sale';
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

            $this->dispatch('guardia-contador-actualizado', tipo: 'salidas', guardiaId: $this->guardia->id);
        }

        $this->cerrarModal();
    }

    public function eliminar(int $id): void
    {
        $salida = $this->guardia->salidasVehiculos()->findOrFail($id);
        $this->authorize('delete', $salida);

        $salida->delete();

        $this->dispatch('guardia-contador-actualizado', tipo: 'salidas', guardiaId: $this->guardia->id);
    }

    // ==================== BOLETA DE CIERRE ====================

    public function abrirBoleta(int $salidaId): void
    {
        $this->resetValidation();
        $this->salida = SalidaVehiculo::with(['vehiculo', 'conductor', 'guardia', 'boletaCierre'])->findOrFail($salidaId);
        $this->authorize('update', $this->salida);
        $this->boletaSalidaId = $salidaId;
        $this->boleta_fecha_entra = ''; // se llena automáticamente si ya existe
        $this->boleta_hora_entra = '';
        $this->boleta_kms_entra = '';
        $this->boleta_observaciones = '';

        // Si ya existe una boleta, cargar sus datos
        $boleta = BoletaCierre::where('salida_id', $salidaId)->first();
        if ($boleta) {
            $this->boleta_fecha_entra = $boleta->fecha_entra->format('Y-m-d');
            $this->boleta_hora_entra = $boleta->hora_entra?->format('H:i') ?? '';
            $this->boleta_kms_entra = (string) ($boleta->kms_entra ?? '');
            $this->boleta_observaciones = $boleta->observaciones ?? '';
        }

        $this->showBoletaModal = true;
    }

    public function cerrarBoletaModal(): void
    {
        $this->showBoletaModal = false;
        $this->boletaSalidaId = null;
        $this->salida = null;
    }

    public function guardarBoleta(): void
    {
        // Obtener la salida para validar kms_sale
        $salida = SalidaVehiculo::findOrFail($this->boletaSalidaId);
        $this->authorize('update', $salida);

        $rules = [
            'boleta_fecha_entra' => 'nullable|date',
            'boleta_hora_entra' => 'nullable|date_format:H:i',
            'boleta_kms_entra' => 'nullable|integer|min:0',
            'boleta_observaciones' => 'nullable|string|max:500',
        ];

        // Si tiene kms_sale y se ingresa un valor, validar que kms_entra sea mayor o igual
        if ($salida->kms_sale !== null && $this->boleta_kms_entra !== '') {
            $rules['boleta_kms_entra'] .= '|gte:' . $salida->kms_sale;
        }

        $data = $this->validate($rules);
        $data['salida_id'] = $this->boletaSalidaId;

        // Crear o actualizar la boleta
        $boleta = BoletaCierre::updateOrCreate(
            ['salida_id' => $this->boletaSalidaId],
            [
                'fecha_entra' => $data['boleta_fecha_entra'],
                'hora_entra' => $data['boleta_hora_entra'],
                'kms_entra' => $data['boleta_kms_entra'],
                'observaciones' => $data['boleta_observaciones'] ?? null,
            ]
        );

        // La relación boleta->salida recalcula automáticamente kms_recorridos y litros

        $this->dispatch('salida-actualizada');
        $this->cerrarBoletaModal();
    }
};