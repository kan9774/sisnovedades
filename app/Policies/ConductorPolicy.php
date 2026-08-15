<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Conductor;

class ConductorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_conductor');
    }

    public function view(User $user, Conductor $conductor): bool
    {
        return $user->HasPermisos('ver_conductor');
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_conductor');
    }

    public function update(User $user, Conductor $conductor): bool
    {
        return $user->HasPermisos('editar_conductor');
    }

    public function delete(User $user, Conductor $conductor): bool
    {
        return $user->HasPermisos('eliminar_conductor');
    }
}
