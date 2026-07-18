<?php

namespace App\Policies;

use App\Models\Guard;
use App\Models\User;
use App\Models\GuardiaPdfDestinatario;

class GuardiaPdfDestinatarioPolicy
{
    /**
     * Determina si el usuario puede ver/crear/actualizar/eliminar destinatarios de PDF.
     * 
     * Acceso permitido para:
     * - Superadmin (is_super_admin = 1)
     * - Miembros de la guardia del día (capitán, oficial, escribiente)
     */
    public function before(User $user, string $ability): ?bool
    {
        // Si es superadmin, siempre permite
        if ($user->is_super_admin) {
            return true;
        }

        // Verificar si es miembro de la guardia del día
        $guardiaHoy = Guard::Hoy()->first();
        if ($guardiaHoy && $guardiaHoy->esMiembro($user)) {
            return true;
        }

        return null; // Dejar que los métodos individuales manejen la lógica
    }

    /**
     * Determina si el usuario puede crear destinatarios de PDF.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $this->isGuardiaDelDia($user);
    }

    /**
     * Determina si el usuario puede actualizar un destinatario de PDF.
     */
    public function update(User $user, GuardiaPdfDestinatario $guardiaPdfDestinatario): bool
    {
        return $user->is_super_admin || $this->isGuardiaDelDia($user);
    }

    /**
     * Determina si el usuario puede eliminar un destinatario de PDF.
     */
    public function delete(User $user, GuardiaPdfDestinatario $guardiaPdfDestinatario): bool
    {
        return $user->is_super_admin || $this->isGuardiaDelDia($user);
    }

    /**
     * Determina si el usuario es miembro de la guardia del día.
     */
    private function isGuardiaDelDia(User $user): bool
    {
        $guardiaHoy = Guard::Hoy()->first();
        return $guardiaHoy ? $guardiaHoy->esMiembro($user) : false;
    }
}