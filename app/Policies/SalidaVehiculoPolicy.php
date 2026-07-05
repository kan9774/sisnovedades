<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SalidaVehiculo;

class SalidaVehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia() || $user->isEscribiente();
    }

    public function view(User $user, SalidaVehiculo $salida): bool
    {
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia() || $user->isEscribiente();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia() || $user->isEscribiente();
    }

    public function update(User $user, SalidaVehiculo $salida): bool
    {
        // Solo admin, capitán u oficial pueden editar
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia();
    }

    public function delete(User $user, SalidaVehiculo $salida): bool
    {
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia();
    }
}