<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehiculo;

class VehiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia();
    }

    public function view(User $user, Vehiculo $vehiculo): bool
    {
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Vehiculo $vehiculo): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Vehiculo $vehiculo): bool
    {
        return $user->isAdmin();
    }
}