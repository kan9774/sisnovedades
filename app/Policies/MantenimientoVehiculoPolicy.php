<?php

namespace App\Policies;

use App\Models\MantenimientoVehiculo;
use App\Models\User;

class MantenimientoVehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_mantenimientos_vehiculos');

    }

    public function view(User $user, MantenimientoVehiculo $mantenimiento): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_mantenimiento');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('crear_mantenimiento_vehiculo');
    }

    public function update(User $user, MantenimientoVehiculo $mantenimiento): bool
    {
        return $user->isAdmin() || $user->HasPermisos('editar_mantenimiento_vehiculo');
    }

    public function delete(User $user, MantenimientoVehiculo $mantenimiento): bool
    {
        return $user->isAdmin() || $user->HasPermisos('eliminar_mantenimiento_vehiculo');
    }
}