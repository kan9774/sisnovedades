<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Permission $permiso): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Permission $permiso): bool
    {
        return $user->isAdmin();
    }
}