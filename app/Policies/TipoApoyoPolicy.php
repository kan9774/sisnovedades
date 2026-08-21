<?php

namespace App\Policies;

use App\Models\TipoApoyo;
use App\Models\User;

class TipoApoyoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_tipos_apoyo');
    }

    public function view(User $user, TipoApoyo $tipoApoyo): bool
    {
        return $user->HasPermisos('ver_tipos_apoyo');
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_tipo_apoyo');
    }

    public function update(User $user, TipoApoyo $tipoApoyo): bool
    {
        return $user->HasPermisos('editar_tipo_apoyo');
    }

    public function delete(User $user, TipoApoyo $tipoApoyo): bool
    {
        return $user->HasPermisos('eliminar_tipo_apoyo');
    }
}
