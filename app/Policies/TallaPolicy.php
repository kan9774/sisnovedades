<?php

namespace App\Policies;

use App\Models\Talla;
use App\Models\User;

class TallaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    public function view(User $user, Talla $talla): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    /**
     * Crear/editar/eliminar tallas queda reservado a admin: es un
     * catálogo estructural del que dependen los ítems existentes.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Talla $talla): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Talla $talla): bool
    {
        return $user->isAdmin();
    }
}