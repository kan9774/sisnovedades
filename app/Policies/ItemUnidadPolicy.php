<?php

namespace App\Policies;

use App\Models\ItemUnidad;
use App\Models\User;

class ItemUnidadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    public function view(User $user, ItemUnidad $itemUnidad): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    /**
     * Dar de alta una nueva unidad física de un item (asignar número de serie, ubicación inicial).
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('crear_item');
    }

    /**
     * Asignar/transferir la unidad a otra ubicación o responsable.
     */
    public function asignar(User $user, ItemUnidad $itemUnidad): bool
    {
        return $user->isAdmin() || $user->HasPermisos('asignar_item_unidad');
    }

    /**
     * Marcar la unidad como en reparación.
     */
    public function marcarEnReparacion(User $user, ItemUnidad $itemUnidad): bool
    {
        return $user->isAdmin() || $user->HasPermisos('reparar_item_unidad');
    }

    /**
     * Dar de baja definitivamente una unidad (rotura, pérdida, destrucción). Acción sensible.
     */
    public function darDeBaja(User $user, ItemUnidad $itemUnidad): bool
    {
        return $user->isAdmin() || $user->HasPermisos('dar_baja_item_unidad');
    }
}