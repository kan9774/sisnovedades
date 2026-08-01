<?php

namespace App\Http\Controllers;

use App\Models\Grado;
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
            $users = User::with(['roles', 'grado'])->get();
        } else {
            // Un admin normal no ve a nadie que tenga el rol admin, ni a SuperAdmins.
            $users = User::with(['roles', 'grado'])
                ->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
                ->where('is_super_admin', false)
                ->get();
        }

        // Orden jerárquico (columna 'orden' del catálogo Grado), no alfabético.
        // Los usuarios sin grado asignado quedan al final.
        $users = $users->sortBy(fn($user) => $user->grado->orden ?? PHP_INT_MAX)->values();

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
        return view('admin.users.create');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        return view('admin.users.edit', compact('user'));
    }

}
