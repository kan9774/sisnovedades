<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Oficina;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use App\Models\Permission;
use App\Models\Unidad;
use Illuminate\Support\Facades\DB;
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

        $baseQuery = auth()->user()->isSuperAdmin()
            // El SuperAdmin ve a todos, incluidos admins y a sí mismo.
            ? User::with(['roles', 'grado'])
            // Un admin normal no ve a nadie que tenga el rol admin, ni a SuperAdmins.
            : User::with(['roles', 'grado'])
                ->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))
                ->where('is_super_admin', false);

        $users = (clone $baseQuery)->whereNotNull('perfil_completo_at')->get();

        // Orden jerárquico (columna 'orden' del catálogo Grado), no alfabético.
        // Los usuarios sin grado asignado quedan al final.
        $users = $users->sortBy(fn($user) => $user->grado->orden ?? PHP_INT_MAX)->values();

        // Usuarios que quedaron a mitad del wizard (Paso 1 o 2 sin
        // terminar): no tienen perfil_completo_at. Se muestran aparte
        // para poder retomarlos o eliminarlos por completo.
        $usersIncompletos = (clone $baseQuery)
            ->whereNull('perfil_completo_at')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.users.index', compact('users', 'usersIncompletos'));
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

    /**
     * Elimina POR COMPLETO (sin pasar por la papelera) a un usuario
     * que quedó a mitad del wizard, junto con cualquier fila de
     * historial que ya se haya generado en el Paso 2 (grado, alta,
     * pase). Pensado para el caso de una C.I. mal tipeada que pasó la
     * validación del dígito verificador pero no corresponde a nadie
     * real: no tiene sentido "retomarlo", hay que poder borrarlo.
     *
     * Solo actúa sobre usuarios con perfil_completo_at en null — un
     * usuario ya activo nunca se puede borrar por esta vía, aunque se
     * adivine el id.
     */
    public function destroyIncompleto($id)
    {
        $user = User::whereNull('perfil_completo_at')->findOrFail($id);

        $this->authorize('delete', $user);

        DB::transaction(function () use ($user) {
            $user->historialGrados()->delete();
            $user->historialEstados()->delete();
            $user->pases()->delete();
            $user->comisiones()->delete();
            $user->forceDelete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Registro incompleto eliminado por completo.');
    }

    public function create($id = null)
    {
        // Si viene un id, es para retomar un usuario incompleto en el
        // paso del wizard que corresponda (ver UserWizard::mount()).
        $user = $id ? User::whereNull('perfil_completo_at')->findOrFail($id) : null;

        return view('admin.users.create', compact('user'));
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