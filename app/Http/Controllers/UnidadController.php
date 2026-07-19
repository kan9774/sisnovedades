<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use Illuminate\Http\Request;

class UnidadController extends Controller
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
        $this->authorize('viewAny', Unidad::class);

        $unidades = Unidad::orderBy('nombre')->paginate(15);
        return view('admin.unidades.index', compact('unidades'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Unidad::class);

        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:unidades,nombre',
        ]);

        $data['activo'] = $request->has('activo');

        Unidad::create($data);

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidad creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Unidad $unidad)
    {
        $this->authorize('view', $unidad);

        $unidad->load(['vehiculos' => function ($query) {
            $query->latest('id')->limit(10);
        }]);

        return view('admin.unidades.show', compact('unidad'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unidad $unidad)
    {
        $this->authorize('delete', $unidad);

        if ($unidad->vehiculos()->count() > 0) {
            return redirect()->route('admin.unidades.index')
                ->with('error', 'No se puede eliminar una unidad con vehículos asociados.');
        }

        $unidad->delete();

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidad eliminada correctamente.');
    }
}
