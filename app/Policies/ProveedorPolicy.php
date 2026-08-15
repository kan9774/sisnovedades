<?php

namespace App\Policies;

use App\Models\Proveedor;
use App\Models\User;

class ProveedorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_proveedores');
    }

    public function view(User $user, Proveedor $proveedor): bool
    {
        return $user->HasPermisos('ver_proveedores');
    }

    /**
     * Crear/editar/eliminar proveedores queda reservado a admin: es un
     * catálogo estructural, igual que Ubicacion.
     */
    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_proveedor');
    }

    public function update(User $user, Proveedor $proveedor): bool
    {
        return $user->HasPermisos('editar_proveedor');
    }

    public function delete(User $user, Proveedor $proveedor): bool
    {
        return $user->HasPermisos('eliminar_proveedor');
    }
}
