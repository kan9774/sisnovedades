<?php

namespace App\Policies;

use App\Models\Apoyo;
use App\Models\User;

class ApoyoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_apoyos');
    }

    public function view(User $user, Apoyo $apoyo): bool
    {
        return $user->HasPermisos('ver_apoyos');
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_apoyo');
    }

    public function update(User $user, Apoyo $apoyo): bool
    {
        return $user->HasPermisos('editar_apoyo');
    }

    public function delete(User $user, Apoyo $apoyo): bool
    {
        return $user->HasPermisos('eliminar_apoyo');
    }
}
