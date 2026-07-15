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