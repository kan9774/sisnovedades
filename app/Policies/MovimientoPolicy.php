<?php

namespace App\Policies;

use App\Models\Movimiento;
use App\Models\User;

class MovimientoPolicy
{
    /**
     * Ver el historial de movimientos.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    public function view(User $user, Movimiento $movimiento): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ver_item');
    }

    public function registrarEntrada(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('registrar_entrada_inventario');
    }

    public function registrarSalida(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('registrar_salida_inventario');
    }

    public function registrarTransferencia(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('registrar_transferencia_inventario');
    }

    /**
     * Ajustar stock tras un conteo físico. Acción sensible: modifica
     * la cantidad "de la nada", así que se restringe más que el resto.
     */
    public function registrarAjuste(User $user): bool
    {
        return $user->isAdmin() || $user->HasPermisos('ajustar_stock_inventario');
    }
}