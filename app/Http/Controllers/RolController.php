<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Permission;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', Rol::class);

        $roles = Rol::with('permisos')
            ->withCount('users')
            ->where('name', '!=', 'admin')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorize('create', Rol::class);

        $permisosPorModulo = $this->agruparPermisosPorModulo(Permission::all());

        return view('admin.roles.create', compact('permisosPorModulo'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Rol::class);

        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:rols,name',
            'description' => 'nullable|string|max:255',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'exists:permissions,id',
        ]);

        $rol = Rol::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (!empty($data['permisos'])) {
            $rol->permisos()->sync($data['permisos']);
        }

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Rol creado correctamente.');
    }

    public function edit(Rol $rol)
    {
        $this->authorize('update', $rol);

        $permisosPorModulo = $this->agruparPermisosPorModulo(Permission::all());

        return view('admin.roles.edit', compact('rol', 'permisosPorModulo'));
    }

    public function update(Request $request, Rol $rol)
    {

        $this->authorize('update', $rol);

        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:rols,name,' . $rol->id,
            'description' => 'nullable|string|max:255',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'exists:permissions,id',
        ]);

        $rol->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $rol->permisos()->sync($data['permisos'] ?? []);

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Rol $rol)
    {
        $this->authorize('delete', $rol);

        if ($rol->name === 'admin') {
            return redirect()->route('admin.roles.index')
                             ->with('error', 'No se puede eliminar el rol admin.');
        }

        if ($rol->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                             ->with('error', 'No se puede eliminar un rol con usuarios asignados.');
        }

        $rol->permisos()->detach();
        $rol->delete();

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Rol eliminado correctamente.');
    }

    /**
     * Agrupa una colección de permisos por su modelo/tabla asociado
     * (columna `permissions.model`, ej: "Guardia", "NovedadPersonal").
     * Los permisos sin modelo asignado caen en el grupo "General".
     *
     * Las claves del resultado ya vienen formateadas para mostrar en la
     * vista (ej: "NovedadPersonal" -> "Novedad Personal"), así que las
     * blades no necesitan tocar la clave, solo mostrarla.
     */
    private function agruparPermisosPorModulo($permisos)
    {
        return $permisos
            ->groupBy(fn ($permiso) => $permiso->model ?: 'General')
            ->sortKeys()
            ->mapWithKeys(function ($grupoDePermisos, $modelo) {
                return [$this->formatearNombreModulo($modelo) => $grupoDePermisos];
            });
    }

    /**
     * Convierte un nombre de modelo en PascalCase (ej: "NovedadPersonal")
     * a un texto legible con espacios (ej: "Novedad Personal").
     */
    private function formatearNombreModulo(string $modelo): string
    {
        if ($modelo === 'General') {
            return 'General';
        }

        return trim(preg_replace('/(?<!^)(?=[A-Z])/', ' ', $modelo));
    }
}