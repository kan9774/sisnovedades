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
     * Puede verlo: miembros de la guardia, admins, o usuarios de la misma oficina.
     */
    public function view(Guard $guardia, News $novedad, Attach $adjunto): RedirectResponse
    {
        // Validar integridad de las relaciones
        abort_if($novedad->guard_id !== $guardia->id, 404);
        abort_if($adjunto->news_id !== $novedad->id, 404);

        // Permisos: admin, miembro de la guardia, o usuario de la misma oficina
        if (
            ! auth()->user()->isAdmin()
            && ! $guardia->esMiembro(auth()->user())
            && $novedad->office_id !== auth()->user()->oficina_id
        ) {
            abort(403, 'No tenés permisos para ver este adjunto.');
        }

        $url = Storage::disk('guardias')->url($adjunto->file_path);

        return redirect($url);
    }

    /**
     * Descargar un adjunto.
     * Puede descargarlo: miembros de la guardia, admins, o usuarios de la misma oficina.
     */
    public function download(Guard $guardia, News $novedad, Attach $adjunto): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Validar integridad de las relaciones
        abort_if($novedad->guard_id !== $guardia->id, 404);
        abort_if($adjunto->news_id !== $novedad->id, 404);

        // Permisos: admin, miembro de la guardia, o usuario de la misma oficina
        if (
            ! auth()->user()->isAdmin()
            && ! $guardia->esMiembro(auth()->user())
            && $novedad->office_id !== auth()->user()->oficina_id
        ) {
            abort(403, 'No tenés permisos para descargar este adjunto.');
        }

        abort_unless(Storage::disk('guardias')->exists($adjunto->file_path), 404);

        return Storage::disk('guardias')->download($adjunto->file_path, $adjunto->file_name);
    }
}