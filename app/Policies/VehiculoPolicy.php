<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehiculo;

class VehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_vehiculo');
    }

    public function view(User $user, Vehiculo $vehiculo): bool
    {
        return $user->HasPermisos('ver_vehiculo') || $user->HasPermisos('ver_vehiculos');
    }

    public function create(User $user): bool
    {
         return $user->HasPermisos('crear_vehiculo');
    }

    public function update(User $user, Vehiculo $vehiculo): bool
    {
       return $user->HasPermisos('editar_vehiculo');
    }

    public function delete(User $user, Vehiculo $vehiculo): bool
    {
       return $user->HasPermisos('eliminar_vehiculo');
    }

    public function restore(User $user, Vehiculo $vehiculo): bool
    {
        return $user->HasPermisos('eliminar_vehiculo');
    }

    public function forceDelete(User $user, Vehiculo $vehiculo): bool
    {
        return $user->HasPermisos('eliminar_vehiculo');
    }
}