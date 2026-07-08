<?php

namespace App\Policies;

use App\Models\TipoVehiculo;
use App\Models\User;

class TipoVehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia();
    }

    public function view(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, TipoVehiculo $tipoVehiculo): bool
    {
        return $user->isAdmin();
    }
}