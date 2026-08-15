<?php

namespace App\Policies;

use App\Models\TipoVehiculo;
use App\Models\User;

class TipoVehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_tipos_vehiculo');
    }

    public function view(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->HasPermisos('ver_tipos_vehiculo');
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_tipo_vehiculo');
    }

    public function update(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->HasPermisos('editar_tipo_vehiculo');
    }

    public function delete(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->HasPermisos('eliminar_tipo_vehiculo');
    }
}
