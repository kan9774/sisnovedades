<?php

namespace App\Http\Controllers;

use App\Models\Attach;
use App\Models\Guard;
use App\Models\News;
use App\Models\Oficina;
use App\Models\Organismo;
use App\Models\User;
use App\Notifications\NovedadUrgenteNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;


class NovedadesController extends Controller
{
    public function index()
    {
        $guardia = Guard::Hoy()
            ->with(['capitan', 'oficial', 'escribiente', 'novedades.escribiente'])
            ->first();

        return view('admin.novedades.index', compact('guardia'));
    }

    public function create(Guard $guardia)
    {
        $this->authorize('create', [News::class, $guardia]);
        abort_if($guardia->status === 'closed', 403, 'La guardia está cerrada');

        $organismos = Organismo::orderBy('name')->get();
        $oficinas = Oficina::where('activo', true)->orderBy('nombre')->get();

        return view('admin.novedades.create', compact('guardia', 'organismos', 'oficinas'));
    }

    public function store(Request $request, Guard $guardia)
    {
        $this->authorize('create', [News::class, $guardia]);
        abort_if($guardia->status === 'closed', 403, 'La guardia está cerrada');

        $data = $request->validate([
            'type'          => 'required|in:Radio,Fax,Correo Electrónico',
            'direction'     => 'required|in:Recibido,Expedido',
            'number'        => 'required|string|max:255',
            'time'          => 'required|date_format:H:i',
            'office_id'     => 'required|exists:oficinas,id',
            'affair'        => 'nullable|string|max:255',
            'text'          => 'required|string',
            'destino'       => 'nullable|string|max:255',
            'clasification' => 'required|in:Rutinario,Prioritario,Urgente,Destello',
            'organismo_id'      => 'nullable|exists:organismos,id',
            'organismo_nuevo'   => 'nullable|string|max:255',
        ]);

        $request->validate([
            'archivo' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10485760', // 10MB
            ],
        ]);

        // Si viene un organismo nuevo, lo creamos
        $organismoId = $data['organismo_id'] ?? null;

        if ($request->filled('organismo_nuevo')) {
            $organismo = Organismo::firstOrCreate(['name' => $request->organismo_nuevo]);
            $organismoId = $organismo->id;
        }

        // Si es Expedido, no hay organismo (somos nosotros)
        if ($data['direction'] === 'Expedido') {
            $organismoId = null;
        }

        $novedad = News::create([
            ...$data,
            'guard_id' => $guardia->id,
            'user_id' => Auth::id(),
            'organismo_id' => $organismoId,
            'estado_atencion' => 'pendiente',
        ]);

        if ($request->hasFile('archivo')) {
            $fecha      = $guardia->date->format('dmY');
            $carpeta    = $data['direction'] === 'Recibido' ? 'Recibidos' : 'Expedidos';
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

        // Notificar a la oficina, sin importar la clasificación de la novedad
        if ($novedad->office_id && $novedad->direction === 'Recibido') {
            $destinatarios = User::where('oficina_id', $novedad->office_id)
                ->where('id', '!=', Auth::id())
                ->get();

            if ($destinatarios->isNotEmpty()) {
                Notification::send($destinatarios, new NovedadUrgenteNotification($novedad));
            }
        }
        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', 'Novedad registrada correctamente.');
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
    public function edit(Guard $guardia, News $novedad)
    {
        $this->authorize('update', $novedad);

        $organismos = Organismo::orderBy('name')->get();
        $oficinas = Oficina::where('activo', true)->orderBy('nombre')->get();

        return view('admin.novedades.edit', compact('guardia', 'novedad', 'organismos', 'oficinas'));
    }
    public function update(Request $request, Guard $guardia, News $novedad)
    {
        $this->authorize('update', $novedad);

        $data = $request->validate([
            'type'          => 'required|in:Radio,Fax,Correo Electrónico',
            'direction'     => 'required|in:Recibido,Expedido',
            'number'        => 'required|string|max:255',
            'time'          => 'required|date_format:H:i',
            'office_id'     => 'required|exists:oficinas,id',
            'affair'        => 'nullable|string|max:255',
            'text'          => 'required|string',
            'destino'       => 'nullable|string|max:255',
            'clasification' => 'required|in:Rutinario,Prioritario,Urgente,Destello',
            'organismo_id'      => 'nullable|exists:organismos,id',
            'organismo_nuevo'   => 'nullable|string|max:255',
        ]);

        // Si viene un organismo nuevo, lo creamos
        $organismoId = $data['organismo_id'] ?? null;

        if ($request->filled('organismo_nuevo')) {
            $organismo = Organismo::firstOrCreate(['name' => $request->organismo_nuevo]);
            $organismoId = $organismo->id;
        }

        // Si es Expedido, no hay organismo (somos nosotros)
        if ($data['direction'] === 'Expedido') {
            $organismoId = null;
        }

        unset($data['organismo_nuevo']);
        $data['organismo_id'] = $organismoId;

        // Subir o eliminar archivo
        $novedad->update($data);
        
        if ($request->hasFile('archivo')) {
            $fecha      = $guardia->date->format('dmY');
            $carpeta    = $data['direction'] === 'Recibido' ? 'Recibidos' : 'Expedidos';
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
        } elseif ($novedad->adjuntos()->where('news_id', $novedad->id)->first()) {
            // Eliminar archivo anterior si no se envió uno nuevo
            $adjunto = $novedad->adjuntos()->where('news_id', $novedad->id)->first();
            Storage::disk('guardias')->delete($adjunto->file_path);
            $adjunto->delete();
        }

        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', 'Novedad actualizada correctamente.');
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
