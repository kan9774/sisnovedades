<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_permisos');
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_permiso');
    }

    public function update(User $user, Permission $permiso): bool
    {
        return $user->HasPermisos('editar_permiso');
    }

    public function delete(User $user, Permission $permiso): bool
    {
        return $user->HasPermisos('eliminar_permiso');
    }
}
