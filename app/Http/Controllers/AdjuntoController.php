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
        // En ambos métodos, después de cargar el modelo $guardia
        $user = auth()->user();

        if (!($guardia->esMiembro($user) || $user->isAdmin())) {
            abort(403, 'No tienes permiso para gestionar adjuntos.');
        }
        // Resto del código para almacenar el adjunto
        $esObligatorio = in_array($novedad->type, ['Fax', 'Correo Electrónico']);

        $request->validate([
            'archivo' => [
                $esObligatorio ? 'required' : 'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240',
            ],
        ]);

        if ($request->hasFile('archivo')) {
            $fecha      = $guardia->date->format('dmY');
            $carpeta    = $novedad->direction === 'Recibido' ? 'Recibidos' : 'Expedidos';
            $directorio = "{$fecha}/{$carpeta}";

            $archivo = $request->file('archivo');
            $nombre  = time() . '_' . $archivo->getClientOriginalName();
            $ruta    = $archivo->storeAs($directorio, $nombre, 'guardias');

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
        $user = auth()->user();
        if (!($guardia->esMiembro($user) || $user->isAdmin())) {
            abort(403, 'No tienes permiso para gestionar adjuntos.');
        }
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
        // Verificar que el adjunto pertenece a la novedad y a la guardia
        abort_if($novedad->guard_id !== $guardia->id, 404);
        abort_if($adjunto->news_id !== $novedad->id, 404);

        // Obtener la URL del archivo en el disco 'guardias'
        $url = Storage::disk('guardias')->url($adjunto->file_path);

        // Redirigir al usuario a la URL del archivo
        return redirect($url);
    }
}
