<?php

namespace App\Policies;

use App\Models\Oficina;
use App\Models\User;

class OficinaPolicy
{
    /**
     * Único lugar donde vive el nombre real de cada permiso de Oficina.
     * Si el nombre del permiso cambia en el seeder, se corrige acá y
     * arrastra automáticamente al menú y al controller.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_oficinas');
    }

    public function view(User $user, Oficina $oficina): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('crear_oficina');
    }

    public function update(User $user, Oficina $oficina): bool
    {
        return $user->isAdmin() || $user->HasPermisos('editar_oficina');
    }

    /**
     * Eliminar oficinas queda estrictamente para admins, sin permiso
     * delegable (así estaba en el controller original, se respeta).
     */
    public function delete(User $user, Oficina $oficina): bool
    {
        return $user->isAdmin();
    }
}