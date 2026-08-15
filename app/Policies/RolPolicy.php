<?php

namespace App\Policies;

use App\Models\Rol;
use App\Models\User;

class RolPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_roles');
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_rol');
    }

    public function update(User $user, Rol $rol): bool
    {
        return $user->HasPermisos('editar_rol');
    }

    public function delete(User $user, Rol $rol): bool
    {
        return $user->HasPermisos('eliminar_rol') && $rol->name !== 'admin';
    }
}
