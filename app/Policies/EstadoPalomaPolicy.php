<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EstadoPaloma;

class EstadoPalomaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_estado_paloma') || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_estado_paloma') || $user->isAdmin();
    }

    public function update(User $user, EstadoPaloma $estado): bool
    {
        return $user->HasPermisos('editar_estado_paloma') || $user->isAdmin();
    }

    public function delete(User $user, EstadoPaloma $estado): bool
    {
        return $user->HasPermisos('eliminar_estado_paloma') || $user->isAdmin();
    }
}