<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use Illuminate\Http\Request;

class ConductorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Conductor::class);
        
        $conductores = Conductor::orderBy('primer_apellido')->paginate(15);
        return view('admin.conductores.index', compact('conductores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Conductor::class);
        
        return view('admin.conductores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Conductor::class);
        
        $data = $request->validate([
            'grado' => 'required|string|max:50',
            'primer_nombre' => 'required|string|max:100',
            'segundo_nombre' => 'nullable|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'documento' => 'required|string|max:20|unique:conductores,documento',
            'nro_licencia' => 'required|string|max:50',
            'categoria_licencia' => 'required|string|max:10',
            'fecha_vencimiento_licencia' => 'required|date|after:today',
            'lugar_carne_salud' => 'nullable|string|max:255',
            'fecha_vencimiento_carne_salud' => 'nullable|date|after:today',
            'lugar_carne_habilitante' => 'nullable|string|max:255',
            'numero_carne_habilitante' => 'nullable|string|max:50',
            'fecha_vencimiento_carne_habilitante' => 'nullable|date|after:today',
            'tipo_vehiculo_habilitado' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
           
        ]);

        $data['activo'] = $request->has('activo');

        Conductor::create($data);

        return redirect()->route('admin.conductores.index')
            ->with('success', 'Conductor creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Conductor $conductor)
    {
        $this->authorize('view', $conductor);
        
        $conductor->load(['salidasVehiculos' => function($query) {
            $query->latest()->limit(10);
        }]);
        
        return view('admin.conductores.show', compact('conductor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Conductor $conductor)
    {
        $this->authorize('update', $conductor);
        
        return view('admin.conductores.edit', compact('conductor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conductor $conductor)
    {
        $this->authorize('update', $conductor);
        
        $data = $request->validate([
            'grado' => 'required|string|max:50',
            'primer_nombre' => 'required|string|max:100',
            'segundo_nombre' => 'nullable|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'documento' => 'required|string|max:20|unique:conductores,documento,' . $conductor->id,
            'nro_licencia' => 'required|string|max:50',
            'categoria_licencia' => 'required|string|max:10',
            'fecha_vencimiento_licencia' => 'required|date|after:today',
            'lugar_carne_salud' => 'nullable|string|max:255',
            'fecha_vencimiento_carne_salud' => 'nullable|date|after:today',
            'lugar_carne_habilitante' => 'nullable|string|max:255',
            'numero_carne_habilitante' => 'nullable|string|max:50',
            'fecha_vencimiento_carne_habilitante' => 'nullable|date|after:today',
            'tipo_vehiculo_habilitado' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $data['activo'] = $request->has('activo');

        $conductor->update($data);

        return redirect()->route('admin.conductores.index')
            ->with('success', 'Conductor actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conductor $conductor)
    {
        $this->authorize('delete', $conductor);
        
        // Verificar si tiene salidas asociadas
        if ($conductor->salidasVehiculos()->count() > 0) {
            return redirect()->route('admin.conductores.index')
                ->with('error', 'No se puede eliminar un conductor con salidas asociadas.');
        }

        $conductor->delete();

        return redirect()->route('admin.conductores.index')
            ->with('success', 'Conductor eliminado correctamente.');
    }
}