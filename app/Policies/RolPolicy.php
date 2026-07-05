<?php

namespace App\Policies;

use App\Models\Rol;
use App\Models\User;

class RolPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Rol $rol): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Rol $rol): bool
    {
        return $user->isAdmin() && $rol->name !== 'admin';
    }
}