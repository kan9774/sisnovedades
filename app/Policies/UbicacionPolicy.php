<?php

namespace App\Policies;

use App\Models\Ubicacion;
use App\Models\User;

class UbicacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    public function view(User $user, Ubicacion $ubicacion): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    /**
     * Crear/editar/eliminar ubicaciones queda reservado a admin: es un
     * catálogo estructural que afecta el stock existente si se borra
     * una ubicación con movimientos asociados.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Ubicacion $ubicacion): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Ubicacion $ubicacion): bool
    {
        return $user->isAdmin();
    }
}