<?php

namespace App\Livewire\Vuelos;

use App\Models\HistorialPaloma;
use App\Models\Vuelo;
use Livewire\Attributes\Locked;
use Livewire\Component;

class VuelosResultados extends Component
{
    #[Locked]
    public int $vueloId;

    public $datosResultados = [];
    public $successMsg = '';
    public $errorMsg = '';
    public $loading = false;

    protected $rules = [
        'datosResultados' => 'required|array',
        'datosResultados.*.distancia_km' => 'nullable|numeric|min:0',
        'datosResultados.*.hora_llegada' => 'required|date_format:H:i',
        'datosResultados.*.posicion' => 'nullable|integer|min:1',
        'datosResultados.*.observaciones' => 'nullable|string',
    ];

    protected $validationAttributes = [
        'datosResultados.*.hora_llegada' => 'hora de llegada',
    ];

    public function mount(int $vueloId)
    {
        $vuelo = Vuelo::with('palomas')->findOrFail($vueloId);

        $this->authorize('update', $vuelo);

        if ($vuelo->estado === 'finalizado') {
            return redirect()->route('admin.vuelos.index')
                ->with('error', 'Este vuelo ya fue finalizado.');
        }

        $this->vueloId = $vueloId;

        $this->datosResultados = [];
        foreach ($vuelo->palomas as $paloma) {
            $this->datosResultados[$paloma->id] = [
                'distancia_km' => $paloma->pivot->distancia_km ?? '',
                'hora_llegada' => optional($paloma->pivot->hora_llegada)->format('H:i') ?? '',
                'posicion' => $paloma->pivot->posicion ?? '',
                'observaciones' => $paloma->pivot->observaciones ?? '',
            ];
        }
    }

    public function guardar()
    {
        $vuelo = Vuelo::with('palomas')->findOrFail($this->vueloId);

        $this->authorize('update', $vuelo);

        $this->validate();

        $this->loading = true;
        $this->errorMsg = '';
        $this->successMsg = '';

        try {
            foreach ($vuelo->palomas as $paloma) {
                $datosPaloma = $this->datosResultados[$paloma->id] ?? [];

                $calculo = $this->calcularTiempoYVelocidad(
                    optional($vuelo->hora_liberacion)->format('H:i'),
                    $datosPaloma['hora_llegada'] ?: null,
                    isset($datosPaloma['distancia_km']) && $datosPaloma['distancia_km'] !== '' ? (float) $datosPaloma['distancia_km'] : null
                );

                $vuelo->palomas()->updateExistingPivot($paloma->id, [
                    'distancia_km' => $datosPaloma['distancia_km'] ?: null,
                    'hora_llegada' => $datosPaloma['hora_llegada'] ?: null,
                    'posicion' => $datosPaloma['posicion'] ?: null,
                    'observaciones' => $datosPaloma['observaciones'] ?: null,
                    'tiempo_vuelo' => $calculo['tiempo_vuelo'],
                    'velocidad_media' => $calculo['velocidad_media'],
                ]);

                $estadoAnteriorId = $paloma->pivot->estado_anterior_id;
                if ($estadoAnteriorId) {
                    $estadoActualId = $paloma->estado_id;
                    $paloma->update(['estado_id' => $estadoAnteriorId]);

                    HistorialPaloma::create([
                        'paloma_id' => $paloma->id,
                        'evento' => 'cambio_estado',
                        'estado_anterior_id' => $estadoActualId,
                        'estado_nuevo_id' => $estadoAnteriorId,
                        'fecha_evento' => now(),
                        'user_id' => auth()->id(),
                        'observaciones' => 'Regreso tras finalizar vuelo #' . $vuelo->id,
                    ]);
                }
            }

            $vuelo->update(['estado' => 'finalizado']);

            $this->successMsg = 'Resultados cargados y vuelo finalizado.';

            return redirect()->route('admin.vuelos.index');
        } catch (\Exception $e) {
            $this->errorMsg = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    protected function calcularTiempoYVelocidad(?string $horaLiberacion, ?string $horaLlegada, ?float $distanciaKm): array
    {
        if (!$horaLiberacion || !$horaLlegada) {
            return ['tiempo_vuelo' => null, 'velocidad_media' => null];
        }

        $liberacion = \Carbon\Carbon::createFromFormat('H:i', $horaLiberacion);
        $llegada = \Carbon\Carbon::createFromFormat('H:i', $horaLlegada);

        if ($llegada->lessThan($liberacion)) {
            $llegada->addDay();
        }

        $diff = $liberacion->diff($llegada);
        $tiempoVuelo = $diff->format('%H:%I:%S');

        $velocidadMedia = null;
        if ($distanciaKm) {
            $horasTotales = $diff->h + ($diff->i / 60) + ($diff->s / 3600);
            if ($horasTotales > 0) {
                $velocidadMedia = round($distanciaKm / $horasTotales, 2);
            }
        }

        return ['tiempo_vuelo' => $tiempoVuelo, 'velocidad_media' => $velocidadMedia];
    }

    public function render()
    {
        $vuelo = Vuelo::with('palomas')->findOrFail($this->vueloId);

        return view('livewire.vuelos.resultados-form', [
            'vuelo' => $vuelo,
        ]);
    }
}
