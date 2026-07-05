<?php

namespace App\Http\Controllers;

use App\Models\Attach;
use App\Models\Guard;
use App\Models\News;
use Illuminate\Support\Facades\Storage;

class VisitanteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $guardias = Guard::cerrada()
            ->with(['capitan', 'oficial'])
            ->orderByDesc('date')
            ->paginate(15);

        return view('web.visitante', compact('guardias'));
    }
    public function show(Guard $guardia)
    {
        // Solo guardias cerradas pueden ser vistas por visitantes
        abort_if($guardia->status !== 'closed', 403);
        $guardia->load(['capitan', 'oficial', 'novedades.adjuntos']);

        return view('web.guardia_detalle', compact('guardia'));
    }
    /*
    @param mixed $novedad
    */
    public function showNovedad(Guard $guardia, News $novedad)
    {
        // Solo guardias cerradas pueden ser vistas por visitantes
        abort_if($guardia->status !== 'closed', 403);
        abort_if($novedad->guard_id !== $guardia->id, 404);
        $novedad->load(['adjuntos', 'escribiente', 'organismo']);

        return view('web.novedad_detalle', compact('guardia', 'novedad'));
    }
    public function downloadAdjunto(Guard $guardia, News $novedad, Attach $adjunto)
    {
        // Implementar la lógica para descargar un adjunto
        abort_if($guardia->status !== 'closed', 403);
        abort_if($novedad->guard_id !== $guardia->id, 404);
        abort_if($adjunto->news_id !== $novedad->id, 404);
        
        return Storage::disk('guardias')->download($adjunto->file_path, $adjunto->file_name);
    }
}