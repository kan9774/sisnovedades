<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectVisitante
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->rol?->name === 'visitante') {
            // Si intenta acceder al backend, redirigir a novedades públicas
            if ($request->is('admin/*')) {
                return redirect()->route('novedades-publicas');
            }
        }

        return $next($request);
    }
}