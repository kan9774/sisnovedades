<?php

namespace App\Http\Controllers;

use App\Models\Palomar;
use Illuminate\Http\Request;

class PalomarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', Palomar::class);
        $palomares = Palomar::withCount('palomas')->get(); // ← get(), no paginate()
        return view('admin.palomar.palomares.index', compact('palomares'));
    }

    public function create()
    {
        $this->authorize('create', Palomar::class);
        return view('admin.palomar.palomares.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Palomar::class);

        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:palomares,nombre',
            'ubicacion' => 'nullable|string|max:255',
            'capacidad_maxima' => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $data['activo'] = $request->has('activo');

        Palomar::create($data);

        return redirect()->route('admin.palomares.index')
            ->with('success', 'Palomar creado correctamente.');
    }

    public function show(Palomar $palomar)
    {
        $this->authorize('view', $palomar);
        $palomar->load('palomas.estado');
        return view('admin.palomar.palomares.show', compact('palomar'));
    }

    public function reporte(Palomar $palomar)
    {
        $this->authorize('view', $palomar);

        $palomar->load('palomas.estado');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.palomar.reporte', compact('palomar'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('parte-diario-' . $palomar->id . '.pdf');
    }

    public function edit(Palomar $palomar)
    {
        $this->authorize('update', $palomar);
        return view('admin.palomar.palomares.edit', compact('palomar'));
    }

    public function update(Request $request, Palomar $palomar)
    {
        $this->authorize('update', $palomar);

        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:palomares,nombre,' . $palomar->id,
            'ubicacion' => 'nullable|string|max:255',
            'capacidad_maxima' => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $data['activo'] = $request->has('activo');

        $palomar->update($data);

        return redirect()->route('admin.palomares.index')
            ->with('success', 'Palomar actualizado correctamente.');
    }

    public function destroy(Palomar $palomar)
    {
        $this->authorize('delete', $palomar);

        // Verificar que no tenga palomas activas
        if ($palomar->palomas()->count() > 0) {
            return redirect()->route('admin.palomares.index')
                ->with('error', 'No se puede eliminar un palomar con palomas asociadas.');
        }

        $palomar->delete();

        return redirect()->route('admin.palomares.index')
            ->with('success', 'Palomar eliminado correctamente.');
    }
}
