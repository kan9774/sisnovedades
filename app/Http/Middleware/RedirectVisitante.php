<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectVisitante
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->rol?->name === 'visitante') {
            // Si intenta acceder al backend, redirigir a la landing pública
            // (las novedades cerradas ya se muestran ahí, vía el componente
            // Livewire de la landing; antes esto apuntaba a la ruta
            // novedades-publicas, que dependía de VisitanteController).
            if ($request->is('admin/*')) {
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}