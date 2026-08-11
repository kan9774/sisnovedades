<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\News;
use App\Services\NovedadService;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Tomar una tarea directamente desde la vista de la novedad
     * (sin pasar por la campanita).
     */
    public function tomar(Guard $guardia, News $novedad)
    {
        $this->authorize('tomar', $novedad);

        NovedadService::tomarSiPendiente($novedad);

        return redirect()->route('admin.guardias.novedades.show', [$guardia, $novedad])
            ->with('success', 'Tarea tomada correctamente.');
    }
}
