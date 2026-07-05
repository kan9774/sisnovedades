<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Conductor;

class ConductorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia();
    }

    public function view(User $user, Conductor $conductor): bool
    {
        return $user->isAdmin() || $user->isCapitan() || $user->isOficialDia();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Conductor $conductor): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Conductor $conductor): bool
    {
        return $user->isAdmin();
    }
}