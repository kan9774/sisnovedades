<?php

namespace App\Http\Controllers;

use App\Models\Oficina;
use Illuminate\Http\Request;

class OficinaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', Oficina::class);

        $oficinas = Oficina::withCount('users')->orderBy('nombre')->paginate(15);

        return view('admin.oficinas.index', compact('oficinas'));
    }

    public function create()
    {
        $this->authorize('create', Oficina::class);

        return view('admin.oficinas.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Oficina::class);

        $data = $request->validate([
            'nombre' => 'required|string|max:150|unique:oficinas,nombre',
            'activo' => 'boolean', // Esto es correcto
        ]);

        // Corrección principal: asignar valor booleano explícito
        $data['activo'] = $request->filled('activo'); // Usamos filled() en lugar de has()

        Oficina::create($data);

        return redirect()->route('admin.oficinas.index')
            ->with('success', 'Oficina creada correctamente.');
    }

    public function edit(Oficina $oficina)
    {
        $this->authorize('update', $oficina);

        return view('admin.oficinas.edit', compact('oficina'));
    }

    public function update(Request $request, Oficina $oficina)
    {
        $this->authorize('update', $oficina);

        $data = $request->validate([
            'nombre' => 'required|string|max:150|unique:oficinas,nombre,' . $oficina->id,
            'activo' => 'boolean',
        ]);

        // Corrección principal: asignar valor booleano explícito
        $data['activo'] = $request->filled('activo');

        $oficina->update($data);

        return redirect()->route('admin.oficinas.index')
            ->with('success', 'Oficina actualizada correctamente.');
    }

    public function destroy(Oficina $oficina)
    {
        $this->authorize('delete', $oficina);

        if ($oficina->users()->count() > 0) {
            return redirect()->route('admin.oficinas.index')
                ->with('error', 'No se puede eliminar una oficina con usuarios asignados.');
        }

        if ($oficina->novedades()->count() > 0) {
            return redirect()->route('admin.oficinas.index')
                ->with('error', 'No se puede eliminar una oficina con novedades asociadas.');
        }

        $oficina->delete();

        return redirect()->route('admin.oficinas.index')
            ->with('success', 'Oficina eliminada correctamente.');
    }
}