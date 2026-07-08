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
        abort_unless(auth()->user()->isAdmin(), 403);

        $oficinas = Oficina::withCount('users')->orderBy('nombre')->paginate(15);

        return view('admin.oficinas.index', compact('oficinas'));
    }

    public function create()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('admin.oficinas.create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

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
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('admin.oficinas.edit', compact('oficina'));
    }

    public function update(Request $request, Oficina $oficina)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

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
        abort_unless(auth()->user()->isAdmin(), 403);

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
