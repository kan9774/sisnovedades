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
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'numero_chasis' => 'nullable|string|max:50|unique:vehiculos,numero_chasis',
            'numero_motor' => 'nullable|string|max:50|unique:vehiculos,numero_motor',
            'ejes' => 'nullable|integer|min:1|max:10',
            'tipo_combustible' => 'required|in:gas_oil,nafta',
            'consumo_litros_por_km' => 'nullable|numeric|min:0|max:999.9999',
            'sin_cuentakilometros' => 'boolean',
            'descripcion' => 'nullable|string|max:255',
            'estado' => 'required|in:verde,amarillo,rojo,negro',
        ]);

        $data['sin_cuentakilometros'] = $request->has('sin_cuentakilometros');
        $data['activo'] = $request->has('activo') && !in_array($data['estado'],['rojo','negro']);

        Vehiculo::create($data);
        $mensaje =$data['activo']
        ? 'Vehículo creado correctamente.'
        : 'Vehículo creado correctamente, pero se encuentra inactivo porque el estado es rojo o negro.';

        return redirect()->route('admin.vehiculos.index')
            ->with('success', $mensaje);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehiculo $vehiculo)
    {
        $this->authorize('view', $vehiculo);

        $vehiculo->load([
            'salidas' => function ($query) {
                $query->with(['guardia', 'conductor'])->latest('id')->limit(10);
            },
            'mantenimientos' => function ($query) {
                $query->latest('fecha')->limit(10);
            },
        ]);

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
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'numero_chasis' => 'nullable|string|max:50|unique:vehiculos,numero_chasis,' . $vehiculo->id,
            'numero_motor' => 'nullable|string|max:50|unique:vehiculos,numero_motor,' . $vehiculo->id,
            'ejes' => 'nullable|integer|min:1|max:10',
            'tipo_combustible' => 'required|in:gas_oil,nafta',
            'consumo_litros_por_km' => 'nullable|numeric|min:0|max:999.9999',
            'descripcion' => 'nullable|string|max:255',
            'estado' => 'required|in:verde,amarillo,rojo,negro',
        ]);

        $data['sin_cuentakilometros'] = $request->has('sin_cuentakilometros');
        $activo = $request->has('activo')&& !in_array($data['estado'],['rojo','negro']);
        $data['activo'] =$activo;
        $vehiculo->update($data);

        $mensaje = $activo
        ? 'Vehículo actualizado correctamente'
        : 'Vehículo actualizado y desactivado automáticamente (estado rojo/negro)';
        return redirect()->route('admin.vehiculos.index')
            ->with('success', $mensaje);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehiculo $vehiculo)
    {
        $this->authorize('delete', $vehiculo);

        // Verificar si tiene salidas asociadas
        if ($vehiculo->salidas()->count() > 0) {
            return redirect()->route('admin.vehiculos.index')
                ->with('error', 'No se puede eliminar un vehículo con salidas asociadas.');
        }

        $vehiculo->delete();

        return redirect()->route('admin.vehiculos.index')
            ->with('success', 'Vehículo eliminado correctamente.');
    }
}
