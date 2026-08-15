<?php

namespace App\Policies;

use App\Models\Grado;
use App\Models\User;

class GradoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_grado');
    }

    public function view(User $user, Grado $grado): bool
    {
        return $user->HasPermisos('ver_grado');
    }

    /**
     * Igual que Categoría: es un catálogo estructural del que depende
     * el personal existente, queda reservado a admin.
     */
    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_grado');
    }

    public function update(User $user, Grado $grado): bool
    {
        return $user->HasPermisos('editar_grado');
    }

    public function delete(User $user, Grado $grado): bool
    {
        return $user->HasPermisos('eliminar_grado');
    }
}
