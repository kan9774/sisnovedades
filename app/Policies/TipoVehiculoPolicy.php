<?php

namespace App\Policies;

use App\Models\TipoVehiculo;
use App\Models\User;

class TipoVehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_tipos_vehiculo') || $user->isSuperAdmin();
    }

    public function view(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->HasPermisos('ver_tipos_vehiculo') || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_tipo_vehiculo') || $user->isSuperAdmin();
    }

    public function update(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->HasPermisos('editar_tipo_vehiculo') || $user->isSuperAdmin();
    }

    public function delete(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->HasPermisos('eliminar_tipo_vehiculo') || $user->isSuperAdmin();
    }
    public function restore(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->isSuperAdmin();
    }
}
