<?php

namespace App\Policies;

use App\Models\Organismo;
use App\Models\User;

class OrganismoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Organismo $organismo): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Organismo $organismo): bool
    {
        return $user->isAdmin();
    }
}
