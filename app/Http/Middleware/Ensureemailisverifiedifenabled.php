<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Envuelve el middleware nativo "verified" de Laravel.
 *
 * Mientras EMAIL_VERIFICATION_ENABLED=false en el .env, deja pasar a
 * cualquiera sin chequear nada (sistema en pruebas). Cuando lo pongas
 * en true, delega en el middleware real de Laravel y empieza a exigir
 * el email confirmado, sin tener que tocar rutas ni controladores.
 */
class EnsureEmailIsVerifiedIfEnabled
{
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null): Response
    {
        if (! config('fortify.email_verification_enabled', false)) {
            return $next($request);
        }

        return app(EnsureEmailIsVerified::class)->handle($request, $next, $redirectToRoute);
    }
}