<?php

namespace App\Policies;

use App\Models\Guard;
use App\Models\User;


class GuardiaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_guardia') || $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Guard $guard): bool
    {
        return $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->HasPermisos('ver_guardia')
            || $guard->esMiembro($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_guardia');
    }

    public function cerrar(User $user, Guard $guardia): bool
    {
        if ($guardia->status === 'closed') {
            return false;
        }
        //Capitan de guardia
        if ($guardia->captain_id === $user->id) {
            return $user->HasPermisos('cerrar_guardia');
        }
        //Oficiales de guardia
        if ($guardia->oficer_id === $user->id) {
            return $user->HasPermisos('cerrar_guardia');
        }

        return $user->isAdmin();
    }
    public function reactivar(User $user, Guard $guardia): bool
    {
        if ($guardia->status === 'open') {
            return false;
        }
        return $guardia->captain_id === $user->id
            || $guardia->oficer_id === $user->id
            || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * Solo pueden editar la guardia quienes ya están designados en ella
     * (capitán, oficial de día, o alguno de los escribientes actuales),
     * o un admin. Sirve, por ejemplo, para que una escribiente que ya
     * figura en la guardia pueda reemplazarse a sí misma por otra si
     * surge un problema. Una vez cerrada, nadie puede editarla.
     */
    public function update(User $user, Guard $guard): bool
    {
        if (!$guard->isAbiertaNoDelete()) {
            return false;
        }

        return $guard->esMiembro($user) || $user->isAdmin();
    }
    /**
     * Determine whether the user can view trashed guards.
     */
    public function viewTrashed(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore a trashed guard.
     */
    public function restore(User $user, Guard $guard): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete a guard.
     */
    public function forceDelete(User $user, Guard $guard): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the model (soft delete).
     */
    public function delete(User $user, Guard $guard): bool
    {
        // Solo Super Admin puede eliminar, y solo si la guardia está cerrada
        return $user->isSuperAdmin() && $guard->status === 'closed';
    }
}