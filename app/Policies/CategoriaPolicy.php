<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\User;

class CategoriaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    public function view(User $user, Categoria $categoria): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    /**
     * Crear/editar/eliminar categorías queda reservado a admin: es un
     * catálogo estructural del que dependen los ítems existentes.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Categoria $categoria): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Categoria $categoria): bool
    {
        return $user->isAdmin();
    }
}