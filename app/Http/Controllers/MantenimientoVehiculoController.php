<?php

namespace App\Http\Controllers;

use App\Models\MantenimientoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class MantenimientoVehiculoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listado de mantenimientos de un vehículo.
     */
    public function index(Vehiculo $vehiculo)
    {
        $this->authorize('viewAny', MantenimientoVehiculo::class);

        $mantenimientos = $vehiculo->mantenimientos()->paginate(15);

        return view('admin.vehiculos.mantenimientos.index', compact('vehiculo', 'mantenimientos'));
    }

    /**
     * Formulario para registrar un mantenimiento.
     */
    public function create(Vehiculo $vehiculo)
    {
        $this->authorize('create', MantenimientoVehiculo::class);

        return view('admin.vehiculos.mantenimientos.create', compact('vehiculo'));
    }

    /**
     * Guardar un mantenimiento nuevo.
     */
    public function store(Request $request, Vehiculo $vehiculo)
    {
        $this->authorize('create', MantenimientoVehiculo::class);

        $data = $request->validate([
            'tipo' => 'required|in:preventivo,correctivo,revision_tecnica,otro',
            'fecha' => 'required|date',
            'kilometraje' => 'nullable|integer|min:0',
            'descripcion' => 'required|string|max:500',
            'costo' => 'nullable|numeric|min:0|max:99999999.99',
            'taller' => 'nullable|string|max:150',
            'proximo_mantenimiento_fecha' => 'nullable|date|after_or_equal:fecha',
            'proximo_mantenimiento_km' => 'nullable|integer|min:0',
        ]);

        $data['vehiculo_id'] = $vehiculo->id;
        $data['registrado_por'] = $request->user()->id;

        MantenimientoVehiculo::create($data);

        return redirect()->route('admin.vehiculos.show', $vehiculo)
            ->with('success', 'Mantenimiento registrado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Vehiculo $vehiculo, MantenimientoVehiculo $mantenimiento)
    {
        $this->authorize('update', $mantenimiento);

        return view('admin.vehiculos.mantenimientos.edit', compact('vehiculo', 'mantenimiento'));
    }

    /**
     * Actualizar un mantenimiento.
     */
    public function update(Request $request, Vehiculo $vehiculo, MantenimientoVehiculo $mantenimiento)
    {
        $this->authorize('update', $mantenimiento);

        $data = $request->validate([
            'tipo' => 'required|in:preventivo,correctivo,revision_tecnica,otro',
            'fecha' => 'required|date',
            'kilometraje' => 'nullable|integer|min:0',
            'descripcion' => 'required|string|max:500',
            'costo' => 'nullable|numeric|min:0|max:99999999.99',
            'taller' => 'nullable|string|max:150',
            'proximo_mantenimiento_fecha' => 'nullable|date|after_or_equal:fecha',
            'proximo_mantenimiento_km' => 'nullable|integer|min:0',
        ]);

        $mantenimiento->update($data);

        return redirect()->route('admin.vehiculos.show', $vehiculo)
            ->with('success', 'Mantenimiento actualizado correctamente.');
    }

    /**
     * Eliminar (soft delete) un mantenimiento.
     */
    public function destroy(Vehiculo $vehiculo, MantenimientoVehiculo $mantenimiento)
    {
        $this->authorize('delete', $mantenimiento);

        $mantenimiento->delete();

        return redirect()->route('admin.vehiculos.show', $vehiculo)
            ->with('success', 'Mantenimiento eliminado correctamente.');
    }
}