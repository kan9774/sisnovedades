<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy de las listas curadas de unidades por módulo (tabla pivot unidad_modulo).
 *
 * Un único permiso gobierna la pantalla: gestionar_unidades_modulo.
 * El SuperAdmin queda exento vía Gate::before (AppServiceProvider).
 */
class UnidadModuloPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('gestionar_unidades_modulo');
    }

    public function update(User $user): bool
    {
        return $user->HasPermisos('gestionar_unidades_modulo');
    }
}
