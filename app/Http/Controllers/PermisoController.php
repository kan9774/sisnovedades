<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', Permission::class);

        $permisos = Permission::withCount('rols')->orderBy('name')->paginate(15);
        return view('admin.permisos.index', compact('permisos'));
    }

    public function create()
    {
        $this->authorize('create', Permission::class);

        return view('admin.permisos.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Permission::class);

        $request->validate([
            'name'       => 'required|string|max:255|unique:permissions,name',
            'description' => 'nullable|string|max:255',
        ]);

        Permission::create([
            'name'       => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.permisos.index')
                         ->with('success', 'Permiso creado correctamente.');
    }

    public function edit(Permission $permiso)
    {
        $this->authorize('update', $permiso);

        return view('admin.permisos.edit', compact('permiso'));
    }

    public function update(Request $request, Permission $permiso)
    {
        $this->authorize('update', $permiso);

        $request->validate([
            'name'       => 'required|string|max:255|unique:permissions,name,' . $permiso->id,
            'description' => 'nullable|string|max:255',
        ]);

        $permiso->update([
            'name'       => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.permisos.index')
                         ->with('success', 'Permiso actualizado correctamente.');
    }

    public function destroy(Permission $permiso)
    {
        $this->authorize('delete', $permiso);

        if ($permiso->rols()->count() > 0) {
            return redirect()->route('admin.permisos.index')
                             ->with('error', 'No se puede eliminar un permiso asignado a roles.');
        }

        $permiso->delete();

        return redirect()->route('admin.permisos.index')
                         ->with('success', 'Permiso eliminado correctamente.');
    }
}