<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Rutas que tienen que quedar siempre accesibles aunque el usuario
     * tenga pendiente el cambio de contraseña (si no, quedaría trabado
     * sin poder ni siquiera guardar la nueva ni cerrar sesión).
     */
    protected const RUTAS_EXENTAS = [
        'password.forzar-cambio',
        'password.forzar-cambio.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->route() && in_array($request->route()->getName(), self::RUTAS_EXENTAS, true)) {
            return $next($request);
        }

        return redirect()->route('password.forzar-cambio');
    }
}