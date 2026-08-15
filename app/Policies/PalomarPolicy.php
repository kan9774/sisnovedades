<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Palomar;

class PalomarPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_palomar');
    }

    public function view(User $user, Palomar $palomar): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_palomar');
    }

    public function update(User $user, Palomar $palomar): bool
    {
        return $user->HasPermisos('editar_palomar');
    }

    public function delete(User $user, Palomar $palomar): bool
    {
        return $user->HasPermisos('eliminar_palomar');
    }
}
