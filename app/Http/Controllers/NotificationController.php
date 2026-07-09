<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\News;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $filtro = $request->query('filtro', 'todas');

        $query = $filtro === 'no_leidas'
            ? Auth::user()->unreadNotifications()
            : Auth::user()->notifications();

        $notificaciones = $query->paginate(15);

        return view('admin.notificaciones.index', compact('notificaciones', 'filtro'));
    }

    /**
     * Abrir una notificación: la marca leída y, si corresponde a una
     * novedad urgente pendiente, la toma para el usuario actual y cierra
     * la notificación para el resto de la oficina.
     */
    public function markAsRead(string $id)
    {
        $notificacion = Auth::user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        $novedadId = $notificacion->data['novedad_id'] ?? null;
        $guardiaId = $notificacion->data['guardia_id'] ?? null;

        if ($novedadId) {
            $this->tomarSiPendiente(News::find($novedadId));
        }

        if ($novedadId && $guardiaId) {
            return redirect()->route('admin.guardias.novedades.show', [$guardiaId, $novedadId]);
        }

        return back();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }

    /**
     * Tomar una tarea directamente desde la vista de la novedad
     * (sin pasar por la campanita).
     */
    public function tomar(Guard $guardia, News $novedad)
    {
        $this->authorize('tomar', $novedad);

        $this->tomarSiPendiente($novedad);

        return redirect()->route('admin.guardias.novedades.show', [$guardia, $novedad])
            ->with('success', 'Tarea tomada correctamente.');
    }
    
    private function tomarSiPendiente(?News $novedad): void
    {
        if (!$novedad || $novedad->estado_atencion !== 'pendiente') {
            return;
        }

        $novedad->estado_atencion = 'visto';
        $novedad->tomado_por_id = Auth::id();
        $novedad->tomado_en = now();
        $novedad->save();

        // Cierra la notificación para el resto de la oficina: la tarea ya fue tomada.
        DatabaseNotification::where('data->novedad_id', $novedad->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
