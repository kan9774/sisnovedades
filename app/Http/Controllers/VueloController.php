<?php

namespace App\Http\Controllers;

use App\Models\EstadoPaloma;
use App\Models\HistorialPaloma;
use App\Models\Paloma;
use App\Models\Vuelo;
use Illuminate\Http\Request;

class VueloController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Vuelo::class);

        $query = Vuelo::with('palomas');

        if ($request->filled('paloma_id')) {
            $query->whereHas('palomas', fn($q) => $q->where('palomas.id', $request->paloma_id));
        }

        $vuelos = $query->orderBy('fecha', 'desc')->paginate(15);
        $palomas = Paloma::whereHas('estado', fn($q) => $q->where('nombre', 'Activa'))->get();

        return view('admin.palomar.vuelos.index', compact('vuelos', 'palomas'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Vuelo::class);

        $palomaIdPreseleccionada = $request->get('paloma_id');
        $palomas = Paloma::whereHas('estado', fn($q) => $q->where('nombre', 'Activa'))->get();

        return view('admin.palomar.vuelos.create', compact('palomas', 'palomaIdPreseleccionada'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Vuelo::class);

        $data = $request->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'tipo' => 'required|in:entrenamiento,competicion',
            'punto_liberacion' => 'nullable|string|max:255',
            'hora_liberacion' => 'nullable|date_format:H:i',
            'condiciones_climaticas' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'palomas' => 'required|array|min:1',
            'palomas.*' => 'exists:palomas,id',
            'datos' => 'nullable|array',
            'datos.*.anilla_competicion' => 'nullable|string|max:50',
        ]);

        $vuelo = Vuelo::create([
            'fecha' => $data['fecha'],
            'tipo' => $data['tipo'],
            'punto_liberacion' => $data['punto_liberacion'] ?? null,
            'hora_liberacion' => $data['hora_liberacion'] ?? null,
            'condiciones_climaticas' => $data['condiciones_climaticas'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'estado' => 'en_curso',
        ]);

        $palomas = Paloma::whereIn('id', $data['palomas'])->get()->keyBy('id');

        $pivotData = [];
        foreach ($data['palomas'] as $palomaId) {
            $pivotData[$palomaId] = [
                'estado_anterior_id' => $palomas[$palomaId]->estado_id,
                'anilla_competicion' => $data['datos'][$palomaId]['anilla_competicion'] ?? null,
            ];
        }
        $vuelo->palomas()->attach($pivotData);

        $this->marcarPalomasEnVuelo($palomas, $data['tipo']);

        return redirect()->route('admin.vuelos.index')
            ->with('success', 'Vuelo registrado. Las palomas fueron marcadas como en vuelo.');
    }

    public function edit(Vuelo $vuelo)
    {
        $this->authorize('update', $vuelo);

        $vuelo->load('palomas');
        $palomas = Paloma::whereHas('estado', fn($q) => $q->where('nombre', 'Activa'))
            ->orWhereIn('id', $vuelo->palomas->pluck('id'))
            ->get()
            ->unique('id');

        return view('admin.palomar.vuelos.edit', compact('vuelo', 'palomas'));
    }

    public function update(Request $request, Vuelo $vuelo)
    {
        $this->authorize('update', $vuelo);

        $data = $request->validate([
            'fecha' => 'required|date',
            'tipo' => 'required|in:entrenamiento,competicion',
            'punto_liberacion' => 'nullable|string|max:255',
            'hora_liberacion' => 'nullable|date_format:H:i',
            'condiciones_climaticas' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'palomas' => 'required|array|min:1',
            'palomas.*' => 'exists:palomas,id',
            'datos' => 'nullable|array',
            'datos.*.anilla_competicion' => 'nullable|string|max:50',
        ]);

        $vuelo->update([
            'fecha' => $data['fecha'],
            'tipo' => $data['tipo'],
            'punto_liberacion' => $data['punto_liberacion'] ?? null,
            'hora_liberacion' => $data['hora_liberacion'] ?? null,
            'condiciones_climaticas' => $data['condiciones_climaticas'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        // La lista de palomas y sus anillas de competición solo se pueden tocar
        // mientras el vuelo sigue en curso (todavía no se cargaron resultados).
        if ($vuelo->estado === 'en_curso') {
            $idsNuevos = collect($data['palomas'])->map(fn($id) => (int) $id);
            $idsActuales = $vuelo->palomas->pluck('id');

            $idsAAgregar = $idsNuevos->diff($idsActuales);
            $idsARemover = $idsActuales->diff($idsNuevos);
            $idsAMantener = $idsNuevos->intersect($idsActuales);

            // Palomas que se sacan del vuelo: vuelven a su estado anterior
            if ($idsARemover->isNotEmpty()) {
                $palomasARemover = Paloma::whereIn('id', $idsARemover)->get()->keyBy('id');

                foreach ($idsARemover as $palomaId) {
                    $pivotExistente = $vuelo->palomas->firstWhere('id', $palomaId)->pivot;
                    $estadoAnteriorId = $pivotExistente->estado_anterior_id;
                    $paloma = $palomasARemover[$palomaId];

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
                            'observaciones' => 'Removida del vuelo #' . $vuelo->id . ' antes de finalizar',
                        ]);
                    }
                }

                $vuelo->palomas()->detach($idsARemover->all());
            }

            // Palomas nuevas: se agregan y pasan a "En competición"/"En vareo"
            if ($idsAAgregar->isNotEmpty()) {
                $palomasAAgregar = Paloma::whereIn('id', $idsAAgregar)->get()->keyBy('id');

                $pivotData = [];
                foreach ($idsAAgregar as $palomaId) {
                    $pivotData[$palomaId] = [
                        'estado_anterior_id' => $palomasAAgregar[$palomaId]->estado_id,
                        'anilla_competicion' => $data['datos'][$palomaId]['anilla_competicion'] ?? null,
                    ];
                }
                $vuelo->palomas()->attach($pivotData);

                $this->marcarPalomasEnVuelo($palomasAAgregar, $data['tipo']);
            }

            // Palomas que se mantienen: solo actualizar la anilla de competición
            foreach ($idsAMantener as $palomaId) {
                $vuelo->palomas()->updateExistingPivot($palomaId, [
                    'anilla_competicion' => $data['datos'][$palomaId]['anilla_competicion'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.vuelos.index')
            ->with('success', 'Vuelo actualizado correctamente.');
    }

    public function resultados(Vuelo $vuelo)
    {
        $this->authorize('update', $vuelo);

        if ($vuelo->estado === 'finalizado') {
            return redirect()->route('admin.vuelos.index')
                ->with('error', 'Este vuelo ya fue finalizado.');
        }

        $vuelo->load('palomas');

        return view('admin.palomar.vuelos.resultados', compact('vuelo'));
    }

    public function guardarResultados(Request $request, Vuelo $vuelo)
    {
        $this->authorize('update', $vuelo);

        $data = $request->validate([
            'datos' => 'required|array',
            'datos.*.distancia_km' => 'nullable|numeric|min:0',
            'datos.*.hora_llegada' => 'nullable|date_format:H:i',
            'datos.*.posicion' => 'nullable|integer|min:1',
            'datos.*.observaciones' => 'nullable|string',
        ]);

        foreach ($vuelo->palomas as $paloma) {
            $datosPaloma = $data['datos'][$paloma->id] ?? [];

            $calculo = $this->calcularTiempoYVelocidad(
                optional($vuelo->hora_liberacion)->format('H:i'),
                $datosPaloma['hora_llegada'] ?? null,
                isset($datosPaloma['distancia_km']) ? (float) $datosPaloma['distancia_km'] : null
            );

            $vuelo->palomas()->updateExistingPivot($paloma->id, [
                'distancia_km' => $datosPaloma['distancia_km'] ?? null,
                'hora_llegada' => $datosPaloma['hora_llegada'] ?? null,
                'posicion' => $datosPaloma['posicion'] ?? null,
                'observaciones' => $datosPaloma['observaciones'] ?? null,
                'tiempo_vuelo' => $calculo['tiempo_vuelo'],
                'velocidad_media' => $calculo['velocidad_media'],
            ]);

            // Devolver la paloma a su estado anterior
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

        return redirect()->route('admin.vuelos.index')
            ->with('success', 'Resultados cargados y vuelo finalizado.');
    }

    public function destroy(Vuelo $vuelo)
    {
        $this->authorize('delete', $vuelo);

        $vuelo->delete(); // el on delete cascade limpia paloma_vuelo

        return redirect()->route('admin.vuelos.index')
            ->with('success', 'Vuelo eliminado correctamente.');
    }

    private function marcarPalomasEnVuelo($palomas, string $tipo): void
    {
        $nombreEstado = $tipo === 'competicion' ? 'En competición' : 'En vareo';

        $estado = EstadoPaloma::firstOrCreate(
            ['nombre' => $nombreEstado],
            ['color' => $tipo === 'competicion' ? '#ffc107' : '#17a2b8', 'activo' => true]
        );

        foreach ($palomas as $paloma) {
            $estadoAnteriorId = $paloma->estado_id;
            $paloma->update(['estado_id' => $estado->id]);

            HistorialPaloma::create([
                'paloma_id' => $paloma->id,
                'evento' => 'cambio_estado',
                'estado_anterior_id' => $estadoAnteriorId,
                'estado_nuevo_id' => $estado->id,
                'fecha_evento' => now(),
                'user_id' => auth()->id(),
                'observaciones' => 'Paloma enviada a ' . strtolower($nombreEstado),
            ]);
        }
    }

    private function calcularTiempoYVelocidad(?string $horaLiberacion, ?string $horaLlegada, ?float $distanciaKm): array
    {
        if (!$horaLiberacion || !$horaLlegada) {
            return ['tiempo_vuelo' => null, 'velocidad_media' => null];
        }

        $liberacion = \Carbon\Carbon::createFromFormat('H:i', $horaLiberacion);
        $llegada = \Carbon\Carbon::createFromFormat('H:i', $horaLlegada);

        if ($llegada->lessThan($liberacion)) {
            $llegada->addDay(); // por si el vuelo cruza medianoche
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
}