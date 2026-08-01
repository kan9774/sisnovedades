<?php

namespace App\Policies;

use App\Models\Unidad;
use App\Models\User;

class UnidadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_unidad');
    }

    public function view(User $user, Unidad $unidad): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_unidad');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('crear_unidad');
    }

    public function update(User $user, Unidad $unidad): bool
    {
        return $user->isAdmin() || $user->HasPermisos('editar_unidad');
    }

    public function delete(User $user, Unidad $unidad): bool
    {
        return $user->isAdmin();
    }
}
