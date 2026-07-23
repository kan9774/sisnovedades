<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehiculo;

class VehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_vehiculo') || $user->isSuperAdmin();
    }

    public function view(User $user, Vehiculo $vehiculo): bool
    {
        return $user->HasPermisos('ver_vehiculo') || $user->HasPermisos('ver_vehiculos') || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
         return $user->HasPermisos('crear_vehiculo') || $user->isSuperAdmin();
    }

    public function update(User $user, Vehiculo $vehiculo): bool
    {
       return $user->HasPermisos('editar_vehiculo') || $user->isSuperAdmin();
    }

    public function delete(User $user, Vehiculo $vehiculo): bool
    {
       return $user->HasPermisos('eliminar_vehiculo') || $user->isSuperAdmin();
    }
}