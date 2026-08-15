<?php

namespace App\Policies;

use App\Models\MantenimientoVehiculo;
use App\Models\User;

class MantenimientoVehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_mantenimiento');
    }

    public function view(User $user, MantenimientoVehiculo $mantenimiento): bool
    {
        return $user->HasPermisos('ver_mantenimiento');
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_mantenimiento_vehiculo');
    }

    public function update(User $user, MantenimientoVehiculo $mantenimiento): bool
    {
        return $user->HasPermisos('editar_mantenimiento_vehiculo');
    }

    public function delete(User $user, MantenimientoVehiculo $mantenimiento): bool
    {
        return $user->HasPermisos('eliminar_mantenimiento_vehiculo');
    }
}
