<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        // Un admin normal no puede editar a un SuperAdmin.
        if ($model->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        return $user->isAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        // Un admin normal no puede eliminar a un SuperAdmin.
        if ($model->isSuperAdmin() && !$user->isSuperAdmin()) {
            return false;
        }

        return $user->isAdmin();
    }
}