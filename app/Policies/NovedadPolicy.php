<?php

namespace App\Policies;

use App\Models\Guard;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NovedadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, News $news): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * IMPORTANTE: si se llama con una guardia específica (p. ej. desde
     * NovedadesController con `$this->authorize('create', [News::class, $guardia])`),
     * se valida membresía sobre ESA guardia puntual.
     * Si no se pasa guardia (p. ej. `@can('create', App\Models\News::class)`
     * en vistas donde ya se sabe que se trabaja con la guardia de hoy),
     * se cae al comportamiento anterior de usar Guard::hoy().
     */
    public function create(User $user, ?Guard $guardia = null): bool
    {
        if (!$user->HasPermisos('registrar_novedad')) {
            return false;
        }

        $guardia = $guardia ?? Guard::hoy()->first();

        if (!$guardia) {
            return false;
        }

        $esCapitan = $guardia->captain_id === $user->id;
        $esOficial = $guardia->oficer_id === $user->id;
        $esEscribiente = $guardia->escribiente()
            ->where('users.id', $user->id)
            ->exists();

        return $esCapitan || $esOficial || $esEscribiente || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, News $news): bool
    {
        // Puede editar la propia
        $guardia = $news->guardia;

        if ($news->user_id === $user->id) {
            return $user->HasPermisos('editar_novedad_propia');
        }
        $perteneceAGuardia = $guardia->captain_id === $user->id
            || $guardia->oficer_id === $user->id;
        if ($perteneceAGuardia) {
            return $user->HasPermisos('editar_cualquier_novedad');
        }
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, News $news): bool
    {
        $guardia = $news->guardia;

        $perteneceAGuardia = $guardia->captain_id === $user->id
            || $guardia->oficer_id === $user->id;

        if ($perteneceAGuardia) {
            return $user->HasPermisos('eliminar_novedad');
        }

        return $user->isAdmin();
    }
    public function tomar(User $user, News $news): bool
    {
        if ($news->estado_atencion !== 'pendiente') {
            return false;
        }

        return $news->office_id === $user->oficina_id || $user->isAdmin();
    }
}
