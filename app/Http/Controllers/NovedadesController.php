<?php

namespace App\Http\Controllers;

use App\Models\Attach;
use App\Models\Guard;
use App\Models\News;
use App\Models\Oficina;
use App\Models\Organismo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $this->authorize('create', News::class);
        abort_if($guardia->status === 'closed', 403, 'La guardia está cerrada');

        $organismos = Organismo::orderBy('name')->get();
        $oficinas = Oficina::where('activo', true)->orderBy('nombre')->get();

        return view('admin.novedades.create', compact('guardia', 'organismos', 'oficinas'));
    }

    public function store(Request $request, Guard $guardia)
    {
        $this->authorize('create', News::class);

        $esCapitan = $guardia->captain_id === Auth::id();
        $esOficial = $guardia->oficer_id === Auth::id();
        $esEscribiente = $guardia->escribiente()
            ->where('users.id', Auth::id())
            ->exists();


        $puedeCrear = $esCapitan || $esOficial || $esEscribiente || Auth::user()->isAdmin();
        abort_if(!$puedeCrear, 403, 'No tienes permisos para registrar novedades en esta guardia');
        abort_if($guardia->status === 'closed', 403, 'La guardia está cerrada');

        $data = $request->validate([
            'type'          => 'required|in:Radio,Fax,Correo Electrónico',
            'direction'     => 'required|in:Recibido,Expedido',
            'number'        => 'required|string|max:255',
            'time'          => 'required|date_format:H:i',
            'office_id'     => 'required|exists:oficinas,id',   // ← debe decir office_id, no office
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
                'max:100000',
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

        ]);

        if ($request->hasFile('archivo')) {
            $fecha      = $guardia->date->format('dmY');
            $carpeta    = $data['direction'] === 'Recibido' ? 'Recibidos' : 'Expedidos';
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

        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', 'Novedad registrada correctamente.');
    }
    public function show(Guard $guardia, News $novedad)
    {
        $novedad->load([
            'escribiente',
            'adjuntos',
            'logs.causer',
            // 'salidas'  ← ELIMINADO

        ]);

        return view('admin.novedades.show', compact('guardia', 'novedad'));
    }
    public function edit(Guard $guardia, News $novedad)
    {
        $this->authorize('update', $novedad);

        return view('admin.novedades.edit', compact('guardia', 'novedad'));
    }
    public function update(Request $request, Guard $guardia, News $novedad)
    {
        $this->authorize('update', $novedad);

        $data = $request->validate([
            'type'          => 'required|in:Radio,Fax,Correo Electrónico',
            'direction'     => 'required|in:Recibido,Expedido',
            'number'        => 'required|string|max:255',
            'time'          => 'required|date_format:H:i',
            'office_id'     => 'required|exists:oficinas,id',   // ← corregir acá también
            'affair'        => 'nullable|string|max:255',
            'text'          => 'required|string',
            'clasification' => 'required|in:Rutinario,Prioritario,Urgente,Destello',
        ]);
        $novedad->update($data);

        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', 'Novedad actualizada correctamente.');
    }
    public function destroy(Guard $guardia, News $novedad)
    {
        $this->authorize('delete', $novedad);

        $novedad->delete($novedad->id);

        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', 'Novedad eliminada correctamente.');
    }
}
