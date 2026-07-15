<?php

namespace App\Policies;

use App\Models\Unidad;
use App\Models\User;

class UnidadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Unidad $unidad): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Unidad $unidad): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Unidad $unidad): bool
    {
        return $user->isAdmin();
    }
}
