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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Unidad::class);

        return view('admin.unidades.create');
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
     * Show the form for editing the specified resource.
     */
    public function edit(Unidad $unidad)
    {
        $this->authorize('update', $unidad);

        return view('admin.unidades.edit', compact('unidad'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unidad $unidad)
    {
        $this->authorize('update', $unidad);

        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:unidades,nombre,' . $unidad->id,
        ]);

        $data['activo'] = $request->has('activo');

        $unidad->update($data);

        return redirect()->route('admin.unidades.index')
            ->with('success', 'Unidad actualizada correctamente.');
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
