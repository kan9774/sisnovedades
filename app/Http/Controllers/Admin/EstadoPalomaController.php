<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EstadoPaloma;
use Illuminate\Http\Request;

class EstadoPalomaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', EstadoPaloma::class);

        $estados = EstadoPaloma::paginate(15);
        return view('admin.palomar.estados.index', compact('estados'));
    }

    public function create()
    {
        $this->authorize('create', EstadoPaloma::class);
        return view('admin.palomar.estados.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', EstadoPaloma::class);
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:estados_paloma',
            'color' => 'nullable|string|max:50',
            'activo' => 'boolean',
        ]);

        $data['activo'] = $request->has('activo');

        EstadoPaloma::create($data);

        return redirect()->route('admin.estados-paloma.index')
            ->with('success', 'Estado creado correctamente.');
    }

    public function edit(EstadoPaloma $estado)
    {
        $this->authorize('update', $estado);
        return view('admin.palomar.estados.edit', compact('estado'));
    }

    public function update(Request $request, EstadoPaloma $estado)
    {
        $this->authorize('update', $estado);
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:estados_paloma,nombre,' . $estado->id,
            'color' => 'nullable|string|max:50',
            'activo' => 'boolean',
        ]);

        $data['activo'] = $request->has('activo');

        $estado->update($data);

        return redirect()->route('admin.estados-paloma.index')
            ->with('success', 'Estado actualizado correctamente.');
    }

    public function destroy(EstadoPaloma $estado)
    {
        $this->authorize('delete', $estado);
        if ($estado->palomas()->count() > 0) {
            return redirect()->route('admin.estados-paloma.index')
                ->with('error', 'No se puede eliminar un estado que tiene palomas asociadas.');
        }

        $estado->delete();

        return redirect()->route('admin.estados-paloma.index')
            ->with('success', 'Estado eliminado correctamente.');
    }
}