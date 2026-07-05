<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vuelo;

class VueloPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_vuelo') || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_vuelo') || $user->isAdmin();
    }

    public function update(User $user, Vuelo $vuelo): bool
    {
        return $user->HasPermisos('editar_vuelo') || $user->isAdmin();
    }

    public function delete(User $user, Vuelo $vuelo): bool
    {
        return $user->HasPermisos('eliminar_vuelo') || $user->isAdmin();
    }
}