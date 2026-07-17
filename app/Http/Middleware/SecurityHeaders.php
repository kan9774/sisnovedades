<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Agregar headers de seguridad a todas las respuestas.
     * Protege contra XSS, clickjacking, MIME sniffing, etc.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        // Prevenir XSS en navegadores antiguos
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Prevenir MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevenir clickjacking (permite solo mismo origen)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Control de acceso a recursos cruzados
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Política de contenido seguro (sin CSP estricto aún para no romper AdminLTE)
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'");

        // Redirigir a HTTPS en producción
        if (app()->environment('production') && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
