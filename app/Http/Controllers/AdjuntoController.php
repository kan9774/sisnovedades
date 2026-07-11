<?php

namespace App\Http\Controllers;

use App\Models\Attach;
use App\Models\Guard;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdjuntoController extends Controller
{
    public function store(Request $request, Guard $guardia, News $novedad)
    {
        $this->authorize('upload-attach', $novedad);

        $request->validate([
            'archivo' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10485760', // 10MB
            ],
        ]);

        if ($request->hasFile('archivo')) {
            $fecha      = $guardia->date->format('dmY');
            $carpeta    = $novedad->direction === 'Recibido' ? 'Recibidos' : 'Expedidos';
            $directorio = "{$fecha}/{$carpeta}";

            $archivo = $request->file('archivo');
            $nombre  = time() . '_' . basename($archivo->getClientOriginalName());
            $ruta    = $archivo->storeAs($directorio, $nombre, 'guardias');
            
            // Añadir thumbnail para imágenes (Laravel 13)
            $mimeType = $archivo->getMimeType();
            if ($mimeType && strpos($mimeType, 'image/') === 0) {
                $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
                $archivo->storeAs($directorio . '/thumbs', $nombreThumb, 'guardias');
            }

            Attach::create([
                'news_id'   => $novedad->id,
                'user_id'   => Auth::id(),
                'file_name' => $archivo->getClientOriginalName(),
                'file_path' => $ruta,
                'file_type' => $archivo->getMimeType(),
                'file_size' => $archivo->getSize(),
            ]);
        }

        return redirect()->route('admin.guardias.novedades.show', [$guardia, $novedad])
            ->with('success', 'Archivo adjuntado correctamente.');
    }

    public function destroy(Guard $guardia, News $novedad, Attach $adjunto)
    {
        $this->authorize('upload-attach', $novedad);

        Storage::disk('guardias')->delete($adjunto->file_path);
        $adjunto->delete();

        return redirect()->route('admin.guardias.novedades.show', [$guardia, $novedad])
            ->with('success', 'Adjunto eliminado correctamente.');
    }

    public function download(Guard $guardia, News $novedad, Attach $adjunto)
    {
        return Storage::disk('guardias')->download($adjunto->file_path, $adjunto->file_name);
    }

    public function view(Guard $guardia, News $novedad, Attach $adjunto)
    {
        abort_if($novedad->guard_id !== $guardia->id, 404);
        abort_if($adjunto->news_id !== $novedad->id, 404);

        $url = Storage::disk('guardias')->url($adjunto->file_path);

        return redirect($url);
    }
}
