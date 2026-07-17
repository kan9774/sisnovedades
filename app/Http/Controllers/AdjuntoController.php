<?php

namespace App\Http\Controllers;

use App\Models\Attach;
use App\Models\Guard;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdjuntoController extends Controller
{
    /**
     * Ver un adjunto (preview).
     * Solo puede verlo quien tenga relación con la guardia.
     */
    public function view(Guard $guardia, News $novedad, Attach $adjunto): RedirectResponse
    {
        // Validar integridad de las relaciones
        abort_if($novedad->guard_id !== $guardia->id, 404);
        abort_if($adjunto->news_id !== $novedad->id, 404);

        // Solo miembros de la guardia o admins pueden ver adjuntos
        if (! auth()->user()->isAdmin() && ! $guardia->esMiembro(auth()->user())) {
            abort(403, 'No tenés permisos para ver este adjunto.');
        }

        $url = Storage::disk('guardias')->url($adjunto->file_path);

        return redirect($url);
    }
}