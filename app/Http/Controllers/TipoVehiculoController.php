<?php

namespace App\Http\Controllers;

use App\Models\TipoVehiculo;
use Illuminate\Http\Request;

class TipoVehiculoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', TipoVehiculo::class);

        $tiposVehiculo = TipoVehiculo::orderBy('nombre')->paginate(15);
        return view('admin.vehiculos.tipos.index', compact('tiposVehiculo'));
    }

    public function create()
    {
        $this->authorize('create', TipoVehiculo::class);

        return view('admin.vehiculos.tipos.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', TipoVehiculo::class);

        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:tipos_vehiculo,nombre',
        ]);

        $data['activo'] = $request->has('activo');

        TipoVehiculo::create($data);

        return redirect()->route('admin.vehiculos.tipos.index')
            ->with('success', 'Tipo de vehículo creado correctamente.');
    }

    public function edit(TipoVehiculo $tipo)
    {
        $this->authorize('update', $tipo);

        return view('admin.vehiculos.tipos.edit', ['tipoVehiculo' => $tipo]);
    }

    public function update(Request $request, TipoVehiculo $tipo)
    {
        $this->authorize('update', $tipo);

        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:tipos_vehiculo,nombre,' . $tipo->id,
        ]);

        $data['activo'] = $request->has('activo');

        $tipo->update($data);

        return redirect()->route('admin.vehiculos.tipos.index')
            ->with('success', 'Tipo de vehículo actualizado correctamente.');
    }

    public function destroy(TipoVehiculo $tipo)
    {
        $this->authorize('delete', $tipo);

        if ($tipo->vehiculos()->count() > 0) {
            return redirect()->route('admin.vehiculos.tipos.index')
                ->with('error', 'No se puede eliminar un tipo de vehículo con vehículos asociados.');
        }

        $tipo->delete();

        return redirect()->route('admin.vehiculos.tipos.index')
            ->with('success', 'Tipo de vehículo eliminado correctamente.');
    }
}