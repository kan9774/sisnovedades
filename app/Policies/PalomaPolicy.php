<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Paloma;

class PalomaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_paloma');
    }

    public function view(User $user, Paloma $paloma): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_paloma');
    }

    public function update(User $user, Paloma $paloma): bool
    {
        return $user->HasPermisos('editar_paloma');
    }

    public function delete(User $user, Paloma $paloma): bool
    {
        return $user->HasPermisos('eliminar_paloma');
    }
}
