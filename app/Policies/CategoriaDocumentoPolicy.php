<?php

namespace App\Policies;

use App\Models\CategoriaDocumento;
use App\Models\User;

class CategoriaDocumentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_documento');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('crear_documento');
    }

    public function update(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('crear_documento');
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('eliminar_documento');
    }
}