<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\User;

class DocumentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_documento');
    }

    public function view(User $user, Documento $documento): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_documento');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('crear_documento');
    }

    public function update(User $user, Documento $documento): bool
    {
        return $user->isAdmin() || $user->HasPermisos('editar_documento');
    }

    public function delete(User $user, Documento $documento): bool
    {
        return $user->isAdmin() || $user->HasPermisos('eliminar_documento');
    }

    public function restore(User $user, Documento $documento): bool
    {
        return $user->isAdmin() || $user->HasPermisos('eliminar_documento');
    }

    public function forceDelete(User $user, Documento $documento): bool
    {
        return $user->isAdmin();
    }
}