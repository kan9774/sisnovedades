<?php

namespace App\Http\Controllers;

use App\Models\Palomar;
use App\Models\Paloma;
use App\Models\EstadoPaloma;
use App\Models\HistorialPaloma;
use Illuminate\Http\Request;

class PalomaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Paloma::class);
        $palomas = Paloma::with(['palomar', 'estado'])->orderBy('anilla')->get();
        return view('admin.palomar.palomas.index', compact('palomas'));
    }

    public function create()
    {
        $this->authorize('create', Paloma::class);

        $palomares = Palomar::where('activo', true)->get();
        $estados = EstadoPaloma::where('activo', true)->get();
        $palomasDisponibles = Paloma::whereHas('estado', fn($q) => $q->where('nombre', 'Activa'))->get();

        return view('admin.palomar.palomas.create', compact('palomares', 'estados', 'palomasDisponibles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Paloma::class);

        $data = $request->validate([
            'palomar_id' => 'required|exists:palomares,id',
            'anilla' => 'required|string|max:50|unique:palomas,anilla',
            'nombre' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'sexo' => 'required|in:macho,hembra,desconocido',
            'color' => 'nullable|string|max:50',
            'raza' => 'nullable|string|max:100',
            'origen' => 'nullable|string|max:255',
            'padre_id' => 'nullable|exists:palomas,id',
            'madre_id' => 'nullable|exists:palomas,id',
            'estado_id' => 'required|exists:estados_paloma,id',
            'observaciones' => 'nullable|string',
        ]);

        $paloma = Paloma::create($data);

        // Registrar historial inicial (nacimiento)
        $paloma->historial()->create([
            'evento' => 'cambio_estado',
            'estado_nuevo_id' => $data['estado_id'],
            'fecha_evento' => now(),
            'user_id' => auth()->id(),
            'observaciones' => 'Registro inicial de la paloma',
        ]);

        return redirect()->route('admin.palomas.index')
            ->with('success', 'Paloma creada correctamente.');
    }

    public function show(Paloma $paloma)
    {
        $this->authorize('view', $paloma);

        $paloma->load([
            'palomar',
            'estado',
            'padre',
            'madre',
            'historial.user',
            'vuelos' => fn($q) => $q->orderBy('vuelos.fecha', 'desc')->limit(10),
        ]);

        return view('admin.palomar.palomas.show', compact('paloma'));
    }

    public function edit(Paloma $paloma)
    {
        $this->authorize('update', $paloma);

        $palomares = Palomar::where('activo', true)->get();
        $estados = EstadoPaloma::where('activo', true)->get();
        $palomasDisponibles = Paloma::where('id', '!=', $paloma->id)->get();

        return view('admin.palomar.palomas.edit', compact('paloma', 'palomares', 'estados', 'palomasDisponibles'));
    }

    public function update(Request $request, Paloma $paloma)
    {
        $this->authorize('update', $paloma); // ← agrega esto

        $data = $request->validate([
            'palomar_id' => 'required|exists:palomares,id',
            'anilla' => 'required|string|max:50|unique:palomas,anilla,' . $paloma->id,
            'nombre' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'required|in:macho,hembra,desconocido',
            'color' => 'nullable|string|max:50',
            'raza' => 'nullable|string|max:100',
            'origen' => 'nullable|string|max:255',
            'padre_id' => 'nullable|exists:palomas,id',
            'madre_id' => 'nullable|exists:palomas,id',
            'estado_id' => 'required|exists:estados_paloma,id',
            'observaciones' => 'nullable|string',
        ]);

        // Evitar autoselección
        if (isset($data['padre_id']) && $data['padre_id'] == $paloma->id) {
            return redirect()->back()->withErrors(['padre_id' => 'No puedes seleccionar la misma paloma como padre.']);
        }
        if (isset($data['madre_id']) && $data['madre_id'] == $paloma->id) {
            return redirect()->back()->withErrors(['madre_id' => 'No puedes seleccionar la misma paloma como madre.']);
        }

        $estadoAnteriorId = $paloma->estado_id;
        $paloma->update($data);

        // Registrar historial si cambió el estado
        if ($estadoAnteriorId != $data['estado_id']) {
            $estadoNuevo = EstadoPaloma::find($data['estado_id']);
            HistorialPaloma::create([
                'paloma_id' => $paloma->id,
                'evento' => $this->determinarEvento($estadoNuevo->nombre),
                'estado_anterior_id' => $estadoAnteriorId,
                'estado_nuevo_id' => $data['estado_id'],
                'fecha_evento' => now(),
                'user_id' => auth()->id(),
                'observaciones' => 'Cambio de estado',
            ]);
        }

        // ✅ REDIRIGIR AL DETALLE DE LA PALOMA
        return redirect()->route('admin.palomas.show', $paloma->id)
            ->with('success', 'Paloma actualizada correctamente.');
    }

    public function destroy(Paloma $paloma)
    {
        $this->authorize('delete', $paloma);

        // Verificar que no tenga vuelos asociados
        if ($paloma->vuelos()->count() > 0) {
            return redirect()->route('admin.palomas.index')
                ->with('error', 'No se puede eliminar una paloma con vuelos registrados.');
        }

        $paloma->delete();

        return redirect()->route('admin.palomas.index')
            ->with('success', 'Paloma eliminada correctamente.');
    }

    private function determinarEvento($estadoNombre)
    {
        $eventos = [
            'Vendida' => 'venta',
            'En préstamo' => 'prestamo',
            'Baja' => 'muerte',
            'Ausente' => 'ausente',
        ];

        return $eventos[$estadoNombre] ?? 'cambio_estado';
    }
}
