<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SalidaVehiculo;

class SalidaVehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isCapitan() || $user->isOficialDia() || $user->isEscribiente()
            || $user->HasPermisos('ver_salida_vehiculo');
    }

    public function view(User $user, SalidaVehiculo $salida): bool
    {
        return $user->isCapitan() || $user->isOficialDia() || $user->isEscribiente()
            || $user->HasPermisos('ver_salida_vehiculo');
    }

    public function create(User $user): bool
    {
        return $user->isCapitan() || $user->isOficialDia() || $user->isEscribiente()
            || $user->HasPermisos('crear_salida_vehiculo');
    }

    public function update(User $user, SalidaVehiculo $salida): bool
    {
        return $user->isCapitan() || $user->isOficialDia() || $user->isEscribiente()
            || $user->HasPermisos('editar_salida_vehiculo');
    }

    public function delete(User $user, SalidaVehiculo $salida): bool
    {
        return $user->isCapitan() || $user->isOficialDia() || $user->isEscribiente()
            || $user->HasPermisos('eliminar_salida_vehiculo');
    }
}