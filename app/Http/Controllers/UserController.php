<?php

namespace App\Http\Controllers;

use App\Models\Oficina;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use App\Models\Permission;
use App\Models\Unidad;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);

        if (auth()->user()->isSuperAdmin()) {
            // El SuperAdmin ve a todos, incluidos admins y a sí mismo.
            $users = User::with('roles')->get();
        } else {
            // Un admin normal no ve a nadie que tenga el rol admin, ni a SuperAdmins.
            $users = User::with('roles')
                ->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
                ->where('is_super_admin', false)
                ->get();
        }

        return view('admin.users.index', compact('users'));
    }

    public function UserDelete()
    {
        $this->authorize('viewAny', User::class);

        $userDelete = User::onlyTrashed()->get();

        return view('admin.users.userdelete', compact('userDelete'));
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrfail($id);
        $this->authorize('delete', $user);

        $user->restore();

        return redirect()->route('admin.users.index')->with('success', 'Usuario restaurado correctamente.');
    }

    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $this->authorize('delete', $user);

        $user->forceDelete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado permanentemente.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $this->authorize('delete', $user);

        if ($user->isAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No se puede eliminar a un administrador.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    public function create()
    {
        $this->authorize('create', User::class);

        if (auth()->user()->isSuperAdmin()) {
            $roles = Rol::all();
        } else {
            $roles = Rol::where('name', '!=', 'admin')->get();
        }
        $unidades = Unidad::where('activo', true)->orderBy('nombre')->get();
        $oficinas = Oficina::where('activo', true)->orderBy('nombre')->get();

        return view('admin.users.create', compact('roles', 'unidades', 'oficinas'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'grade'      => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:6|confirmed',
            'roles'      => 'required|array|min:1',
            'roles.*'    => 'exists:rols,id',
            'unidad_id'  => 'required|exists:unidades,id',
            'oficina_id' => 'nullable|exists:oficinas,id',
        ]);

        $isSuperAdmin = $request->boolean('is_super_admin') && auth()->user()->isSuperAdmin();

        // Solo un SuperAdmin puede asignar el rol "admin", aunque alguien
        // manipule el form y mande ese id igual.
        $roles = $this->filtrarRolesPermitidos($data['roles']);

        $user = User::create([
            'name'           => $data['name'],
            'last_name'      => $data['last_name'],
            'grade'          => $data['grade'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'unidad_id'      => $data['unidad_id'],
            'status'         => 'active',
            'is_super_admin' => $isSuperAdmin,
            'oficina_id'     => $data['oficina_id'] ?? null,
            // Vos definiste la contraseña a mano, así que la tiene que
            // cambiar apenas entre por primera vez.
            'must_change_password' => true,
        ]);

        $user->roles()->sync($roles);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        $this->authorize('update', $user);

        if (auth()->user()->isSuperAdmin()) {
            $roles = Rol::all();
        } else {
            $roles = Rol::where('name', '!=', 'admin')->get();
        }

        $permisos = Permission::orderBy('name')->get();
        $unidades = Unidad::where('activo', true)
            ->orWhere('id', $user->unidad_id)
            ->orderBy('nombre')
            ->get();
        $oficinas = Oficina::where('activo', true)
            ->orWhere('id', $user->oficina_id)
            ->orderBy('nombre')
            ->get();

        return view('admin.users.edit', compact('user', 'roles', 'permisos', 'unidades', 'oficinas'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('assignPermissions', $user);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'grade'      => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'roles'      => 'required|array|min:1',
            'roles.*'    => 'exists:rols,id',
            'unidad_id'  => 'required|exists:unidades,id',
            'password'   => 'nullable|string|min:6|confirmed',
            'permisos_directos'   => 'nullable|array',
            'permisos_directos.*' => 'exists:permissions,id',
            'oficina_id' => 'nullable|exists:oficinas,id',
        ]);

        $user->name      = $data['name'];
        $user->last_name = $data['last_name'];
        $user->grade     = $data['grade'];
        $user->email     = $data['email'];
        $user->unidad_id = $data['unidad_id'];
        $user->oficina_id = $data['oficina_id'] ?? null;

        if ($request->filled('password')) {
            $user->password = Hash::make($data['password']);
            // Fue el admin quien la definió, así que la tiene que cambiar
            // en su próximo login.
            $user->must_change_password = true;
        }

        $user->save();

        $roles = $this->filtrarRolesPermitidos($data['roles']);
        $user->roles()->sync($roles);

        // Solo un admin puede otorgar permisos individuales, para evitar que
        // alguien con permiso de "editar usuarios" se autoasigne privilegios extra.
        if (auth()->user()->isAdmin()) {
            $user->permisosDirectos()->sync($data['permisos_directos'] ?? []);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Quita el rol "admin" del listado si quien está armando/editando el
     * usuario no es SuperAdmin, sin importar lo que haya venido en el form.
     *
     * @param array<int> $rolesIds
     * @return array<int>
     */
    private function filtrarRolesPermitidos(array $rolesIds): array
    {
        if (auth()->user()->isSuperAdmin()) {
            return $rolesIds;
        }

        $adminRolId = Rol::where('name', 'admin')->value('id');

        return array_values(array_diff($rolesIds, [$adminRolId]));
    }
}