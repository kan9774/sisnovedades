<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use App\Models\Permission;
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
            $users = User::with('rol')->get();
        } else {
            // Un admin normal no ve admins ni SuperAdmins.
            $users = User::with('rol')
                ->whereHas('rol', fn($q) => $q->where('name', '!=', 'admin'))
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
        $roles = Rol::where('name', '!=', 'admin')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        // Validate the request
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'grade'     => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
            'rol_id'    => 'required|exists:rols,id',
        ]);

        // Solo un SuperAdmin puede crear a otro SuperAdmin.
        // Si el request trae ese flag pero quien lo crea no es SuperAdmin, se ignora.
        $isSuperAdmin = $request->boolean('is_super_admin') && auth()->user()->isSuperAdmin();

        User::create([
            'name'           => $data['name'],
            'last_name'      => $data['last_name'],
            'grade'          => $data['grade'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'rol_id'         => $data['rol_id'],
            'status'         => 'active',
            'is_super_admin' => $isSuperAdmin,
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Usuario creado correctamente.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $roles = Rol::where('name','!=','admin')->get();
        $permisos = Permission::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles', 'permisos'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'grade'     => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'rol_id'    => 'required|exists:rols,id',
            'password'  => 'nullable|string|min:6|confirmed',
            'permisos_directos'   => 'nullable|array',
            'permisos_directos.*' => 'exists:permissions,id',
        ]);

        $user->name      = $data['name'];
        $user->last_name = $data['last_name'];
        $user->grade     = $data['grade'];
        $user->email     = $data['email'];
        $user->rol_id    = $data['rol_id'];

        if ($request->filled('password')) {
            $user->password = Hash::make($data['password']);
        }

        // Solo un SuperAdmin puede otorgar o quitar el flag de SuperAdmin a otro usuario.
        // Nadie puede quitarse el flag a sí mismo (evita quedarse sin acceso por error).
        if (auth()->user()->isSuperAdmin() && $user->id !== auth()->id()) {
            $user->is_super_admin = $request->boolean('is_super_admin');
        }

        $user->save();

        // Solo un admin puede otorgar permisos individuales, para evitar que
        // alguien con permiso de "editar usuarios" se autoasigne privilegios extra.
        if (auth()->user()->isAdmin()) {
            $user->permisosDirectos()->sync($data['permisos_directos'] ?? []);
        }

        return redirect()->route('admin.users.index')
                         ->with('success', 'Usuario actualizado correctamente.');
    }
}