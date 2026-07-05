<?php

namespace App\Http\Controllers;

use App\Models\Organismo;
use Illuminate\Http\Request;

class OrganismoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $organismos = Organismo::orderBy('name')->paginate(15);
        return view('admin.organismos.index', compact('organismos'));
    }

    public function create()
    {
        return view('admin.organismos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organismos,name',
        ]);

        Organismo::create(['name' => $request->name]);

        return redirect()->route('admin.organismos.index')
                         ->with('success', 'Organismo creado correctamente.');
    }

    public function edit(Organismo $organismo)
    {
        return view('admin.organismos.edit', compact('organismo'));
    }

    public function update(Request $request, Organismo $organismo)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organismos,name,' . $organismo->id,
        ]);

        $organismo->update(['name' => $request->name]);

        return redirect()->route('admin.organismos.index')
                         ->with('success', 'Organismo actualizado correctamente.');
    }

    public function destroy(Organismo $organismo)
    {
        if ($organismo->novedades()->count() > 0) {
            return redirect()->route('admin.organismos.index')
                             ->with('error', 'No se puede eliminar un organismo con novedades asociadas.');
        }

        $organismo->delete();

        return redirect()->route('admin.organismos.index')
                         ->with('success', 'Organismo eliminado correctamente.');
    }
}