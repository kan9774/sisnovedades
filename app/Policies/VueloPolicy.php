<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vuelo;

class VueloPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_vuelo');
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_vuelo');
    }

    public function update(User $user, Vuelo $vuelo): bool
    {
        return $user->HasPermisos('editar_vuelo');
    }

    public function delete(User $user, Vuelo $vuelo): bool
    {
        return $user->HasPermisos('eliminar_vuelo');
    }
}
