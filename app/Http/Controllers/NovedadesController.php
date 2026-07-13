<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\News;
use Illuminate\Support\Facades\Storage;

class NovedadesController extends Controller
{
    public function index()
    {
        $guardia = Guard::Hoy()
            ->with(['capitan', 'oficial', 'escribiente', 'novedades.escribiente'])
            ->first();

        $puedeOperarGuardia = false;

        if ($guardia) {
            $puedeOperarGuardia =
                $guardia->captain_id === auth()->id() ||
                $guardia->oficer_id === auth()->id() ||
                $guardia->escribiente->contains('id', auth()->id()) ||
                auth()->user()->isAdmin();
        }

        return view('admin.novedades.index', compact('guardia', 'puedeOperarGuardia'));
    }

    public function show(Guard $guardia, News $novedad)
    {
        $novedad->load([
            'escribiente',
            'adjuntos',
            'logs.causer',
            'tomadoPor',
        ]);

        return view('admin.novedades.show', compact('guardia', 'novedad'));
    }

    public function destroy(Guard $guardia, News $novedad)
    {
        $this->authorize('delete', $novedad);

        // Eliminar adjuntos
        foreach ($novedad->adjuntos as $adjunto) {
            Storage::disk('guardias')->delete($adjunto->file_path);
            $adjunto->delete();
        }

        $novedad->delete();

        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', 'Novedad eliminada correctamente.');
    }
}