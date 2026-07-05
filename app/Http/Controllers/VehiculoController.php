<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VehiculoController extends Controller
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
        $this->authorize('viewAny', Vehiculo::class);
        
        $vehiculos = Vehiculo::orderBy('matricula')->paginate(15);
        return view('admin.vehiculos.index', compact('vehiculos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Vehiculo::class);
        
        return view('admin.vehiculos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Vehiculo::class);
        
        $data = $request->validate([
            'matricula' => 'required|string|max:20|unique:vehiculos,matricula',
            'tipo_combustible' => 'required|in:gas_oil,nafta',
            'consumo_litros_por_km' => 'nullable|numeric|min:0|max:999.9999',            'sin_cuentakilometros' => 'boolean',
            'descripcion' => 'nullable|string|max:255',

        ]);

        $data['sin_cuentakilometros'] = $request->has('sin_cuentakilometros');
        $data['activo'] = $request->has('activo');

        Vehiculo::create($data);

        return redirect()->route('admin.vehiculos.index')
            ->with('success', 'Vehículo creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehiculo $vehiculo)
    {
        $this->authorize('view', $vehiculo);
        
        $vehiculo->load(['novedades' => function($query) {
            $query->latest()->limit(10);
        }]);
        
        return view('admin.vehiculos.show', compact('vehiculo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehiculo $vehiculo)
    {
        $this->authorize('update', $vehiculo);
        
        return view('admin.vehiculos.edit', compact('vehiculo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehiculo $vehiculo)
    {
        $this->authorize('update', $vehiculo);
        
        $data = $request->validate([
            'matricula' => 'required|string|max:20|unique:vehiculos,matricula,' . $vehiculo->id,
            'tipo_combustible' => 'required|in:gas_oil,nafta',
            'consumo_litros_por_km' => 'nullable|numeric|min:0|max:999.9999',
            'descripcion' => 'nullable|string|max:255',
            
        ]);

        $data['sin_cuentakilometros'] = $request->has('sin_cuentakilometros');
        $data['activo'] = $request->has('activo');

        $vehiculo->update($data);

        return redirect()->route('admin.vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehiculo $vehiculo)
    {
        $this->authorize('delete', $vehiculo);
        
        // Verificar si tiene salidas asociadas
        if ($vehiculo->novedades()->count() > 0) {
            return redirect()->route('admin.vehiculos.index')
                ->with('error', 'No se puede eliminar un vehículo con salidas asociadas.');
        }

        $vehiculo->delete();

        return redirect()->route('admin.vehiculos.index')
            ->with('success', 'Vehículo eliminado correctamente.');
    }
}