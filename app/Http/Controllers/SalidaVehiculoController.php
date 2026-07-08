<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\SalidaVehiculo;
use App\Models\Vehiculo;
use App\Models\Conductor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalidaVehiculoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario para crear una nueva salida.
     */
    public function create(Guard $guardia)
    {
        $this->authorize('create', SalidaVehiculo::class);

        $esCapitan = $guardia->captain_id === Auth::id();
        $esOficial = $guardia->oficer_id === Auth::id();
        $esEscribiente = $guardia->escribiente()
            ->where('users.id', Auth::id())
            ->exists();

        $puedeCrear = $esCapitan || $esOficial || $esEscribiente || Auth::user()->isAdmin();
        abort_if(!$puedeCrear, 403, 'No tienes permisos para registrar salidas de vehículo en esta guardia');

        $vehiculos = Vehiculo::where('activo', true)->orderBy('matricula')->get();
        $conductores = Conductor::where('activo', true)->orderBy('primer_apellido')->get();

        return view('admin.guardias.salidas.create', compact('guardia', 'vehiculos', 'conductores'));
    }

    /**
     * Guardar una nueva salida.
     */
    public function store(Request $request, Guard $guardia)
    {
        $this->authorize('create', SalidaVehiculo::class);

        $esCapitan = $guardia->captain_id === Auth::id();
        $esOficial = $guardia->oficer_id === Auth::id();
        $esEscribiente = $guardia->escribiente()
            ->where('users.id', Auth::id())
            ->exists();

        $puedeCrear = $esCapitan || $esOficial || $esEscribiente || Auth::user()->isAdmin();
        abort_if(!$puedeCrear, 403, 'No tienes permisos para registrar salidas de vehículo en esta guardia');
        abort_if($guardia->status === 'closed', 403, 'La guardia está cerrada');

        $data = $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'conductor_id' => 'required|exists:conductores,id',
            'tipo_combustible' => 'required|in:gas_oil,nafta',
            'hora_sale' => 'required|date_format:H:i',
            'hora_entra' => 'nullable|date_format:H:i|after:hora_sale',
            'kms_sale' => 'nullable|integer|min:0',
            'kms_entra' => 'nullable|integer|min:0|gt:kms_sale',
            'comision' => 'required|string',
        ]);

        // Validación condicional según vehículo
        $vehiculo = Vehiculo::find($data['vehiculo_id']);
        if (!$vehiculo->sin_cuentakilometros) {
            $request->validate([
                'kms_sale' => 'required|integer|min:0',
                'kms_entra' => 'required|integer|min:0|gt:kms_sale',
            ]);
        }

        $data['guardia_id'] = $guardia->id;

        SalidaVehiculo::create($data);

        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', 'Salida de vehículo registrada correctamente.');
    }

    /**
     * Mostrar formulario para editar una salida.
     */
    public function edit(Guard $guardia, SalidaVehiculo $salida)
    {
        $this->authorize('update', $salida);

        $vehiculos = Vehiculo::where('activo', true)->orderBy('matricula')->get();
        $conductores = Conductor::where('activo', true)->orderBy('primer_apellido')->get();

        return view('admin.guardias.salidas.edit', compact('guardia', 'salida', 'vehiculos', 'conductores'));
    }

    /**
     * Actualizar una salida.
     */
    public function update(Request $request, Guard $guardia, SalidaVehiculo $salida)
    {
        $this->authorize('update', $salida);

        $data = $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'conductor_id' => 'required|exists:conductores,id',
            'tipo_combustible' => 'required|in:gas_oil,nafta',
            'hora_sale' => 'required|date_format:H:i',
            'hora_entra' => 'nullable|date_format:H:i|after:hora_sale',
            'kms_sale' => 'nullable|integer|min:0',
            'kms_entra' => 'nullable|integer|min:0|gt:kms_sale',
            'comision' => 'required|string',
        ]);

        $vehiculo = Vehiculo::find($data['vehiculo_id']);
        if (!$vehiculo->sin_cuentakilometros) {
            $request->validate([
                'kms_sale' => 'required|integer|min:0',
                'kms_entra' => 'required|integer|min:0|gt:kms_sale',
            ]);
        }

        $salida->update($data);

        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', 'Salida actualizada correctamente.');
    }

    /**
     * Eliminar una salida (soft delete si aplica, o eliminar físicamente).
     */
    public function destroy(Guard $guardia, SalidaVehiculo $salida)
    {
        $this->authorize('delete', $salida);

        $salida->delete();

        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', 'Salida eliminada correctamente.');
    }
}
