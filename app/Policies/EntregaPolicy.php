<?php

namespace App\Policies;

use App\Models\Entrega;
use App\Models\User;

class EntregaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    public function view(User $user, Entrega $entrega): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    /**
     * Procesar una entrega/devolución. Por dentro puede mover ítems por
     * cantidad (equivalente a registrarTransferencia) y/o unidades
     * individuales (equivalente a asignar), así que alcanza con tener
     * uno de los dos permisos para acceder a la pantalla — el carrito
     * puede terminar teniendo solo un tipo de línea.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin()
            || $user->HasPermisos('registrar_transferencia_inventario')
            || $user->HasPermisos('asignar_item_unidad');
    }
}