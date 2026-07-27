<?php

namespace App\Policies;

use App\Models\Talla;
use App\Models\User;

class TallaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_tallas');
    }

    public function view(User $user, Talla $talla): bool
    {
        return $user->isAdmin() || $user->HasPermisos('crear_talla');
    }

    /**
     * Crear/editar/eliminar tallas queda reservado a admin: es un
     * catálogo estructural del que dependen los ítems existentes.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin()|| $user->HasPermisos('crear_talla');
    }

    public function update(User $user, Talla $talla): bool
    {
        return $user->isAdmin() || $user->HasPermisos('editar_talla');
    }

    public function delete(User $user, Talla $talla): bool
    {
        return $user->isAdmin() || $user->HasPermisos('eliminar_talla');
    }
}