<?php

namespace App\Policies;

use App\Models\Organismo;
use App\Models\User;

class OrganismoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_organismos');
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_organismo');
    }

    public function update(User $user, Organismo $organismo): bool
    {
        return $user->HasPermisos('editar_organismo');
    }

    public function delete(User $user, Organismo $organismo): bool
    {
        return $user->HasPermisos('eliminar_organismo');
    }
}
