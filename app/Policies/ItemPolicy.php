<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\ItemUnidad;
use App\Models\User;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->HasPermisos('ver_item');
    }

    public function view(User $user, Item $item): bool
    {
        return $user->HasPermisos('ver_item');
    }

    public function create(User $user): bool
    {
        return $user->HasPermisos('crear_item');
    }

    public function update(User $user, Item $item): bool
    {
        return $user->HasPermisos('editar_item');
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->HasPermisos('eliminar_item');
    }
    /**
     * Marcar la unidad como en reparación.
     */
    public function marcarEnReparacion(User $user, ItemUnidad $itemUnidad): bool
    {
        return $user->HasPermisos('reparar_item_unidad');
    }

    /**
     * Volver a marcar como disponible una unidad que estaba en reparación.
     */
    public function volverDeReparacion(User $user, ItemUnidad $itemUnidad): bool
    {
        return $user->HasPermisos('reparar_item_unidad');
    }
}
