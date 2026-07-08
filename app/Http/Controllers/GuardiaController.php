<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\TipoVehiculo;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GuardiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $guardias = Guard::with(['capitan', 'oficial'])
            ->withCount('novedades')
            ->orderbydesc('date')
            ->paginate(15);
        return view('admin.guardias.index', compact('guardias'));
    }

    /**
     * Display a listing of trashed guards.
     */
    public function trashed()
    {
        $this->authorize('viewTrashed', Guard::class);

        $guardias = Guard::onlyTrashed()
            ->with(['capitan', 'oficial'])
            ->withCount('novedades')
            ->orderByDesc('date')
            ->paginate(15);

        return view('admin.guardias.trashed', compact('guardias'));
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Guard $guardia)
    {
        $this->authorize('delete', $guardia);

        // Verificar si la guardia ya está cerrada
        if ($guardia->status === 'open') {
            return redirect()->route('admin.guardias.index')
                ->with('error', 'No se puede eliminar una guardia abierta. Ciérrala primero.');
        }

        // Soft delete (no elimina las novedades ni adjuntos)
        $guardia->delete();

        return redirect()->route('admin.guardias.index')
            ->with('success', 'Guardia eliminada correctamente. Puedes restaurarla desde la papelera.');
    }

    /**
     * Restore a trashed guard.
     */
    public function restore($id)
    {
        $guardia = Guard::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $guardia);

        $guardia->restore();

        return redirect()->route('admin.guardias.index')
            ->with('success', 'Guardia restaurada correctamente.');
    }

    /**
     * Permanently delete a trashed guard.
     */

    public function forceDelete($id)
    {
        $guardia = Guard::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $guardia);

        DB::transaction(function () use ($guardia) {
            $guardia->load('novedades.adjuntos', 'novedades.logs', 'salidasVehiculos');

            foreach ($guardia->novedades as $novedad) {
                foreach ($novedad->adjuntos as $adjunto) {
                    Storage::disk('guardias')->delete($adjunto->file_path);
                    $adjunto->delete();
                }

                $novedad->delete();
            }

            $guardia->salidasVehiculos()->delete();
            $guardia->escribiente()->detach();
            $guardia->forceDelete();
        });

        return redirect()->route('admin.guardias.trashed')
            ->with('success', 'Guardia, novedades, adjuntos y salidas de vehículo eliminados permanentemente.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $this->authorize('create', Guard::class);
        $capitanes = User::whereHas('rol', fn($q) => $q->where('name', 'capitan_de_servicio'))->get();
        $oficiales = User::whereHas('rol', fn($q) => $q->where('name', 'oficial_de_dia'))->get();
        $escribientes = User::whereHas('rol', fn($q) => $q->where('name', 'escribiente'))->get();
        $tiposVehiculo = TipoVehiculo::where('activo', true)->orderBy('nombre')->get();

        return view('admin.guardias.create', compact('capitanes', 'oficiales', 'escribientes', 'tiposVehiculo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $this->authorize('create', Guard::class);
        $data = $request->validate([
            'date' => 'required|date|unique:guards,date',
            'captain_id' => 'required|exists:users,id',
            'oficer_id' => 'required|exists:users,id',
            'escribiente_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Si quien crea la guardia es escribiente, siempre queda él/ella asignado,
        // sin importar qué se haya enviado en el formulario.
        $escribienteId = Auth::user()->isEscribiente()
            ? Auth::id()
            : $data['escribiente_id'];

        $guardia = Guard::create([
            'date' => $data['date'],
            'captain_id' => $data['captain_id'],
            'oficer_id' => $data['oficer_id'],
            'status' => 'open',
            'notes' => $data['notes'] ?? null,
        ]);
        $guardia->escribiente()->attach($escribienteId);
        return redirect()->route('admin.guardias.show', $guardia)->with('success', 'Guardia creada exitosamente');
    }

    public function show(Guard $guardia)
    {
        $guardia->load([
            'capitan',
            'oficial',
            'escribiente',
            'salidasVehiculos.vehiculo',
            'salidasVehiculos.conductor',
        ]);

        return view('admin.guardias.show', compact('guardia'));
    }

    public function Hoy()
    {
        $guardia = Guard::Hoy('date', now()->toDateString())->first();
        if (!$guardia) {
            return redirect()->route('admin.guardias.index')->with('error', 'No hay guardia para hoy');
        }
        return redirect()->route('admin.guardias.show', $guardia);
    }

    public function reactivar(Guard $guardia)
    {
        $this->authorize('reactivar', $guardia);
        $guardia->update([
            'status' => 'open',
            'closed_at' => null,
        ]);
        return redirect()->route('admin.guardias.show', $guardia)->with('success', 'Guardia reactivada exitosamente');
    }

    /**
     * Cerrar una guardia (cambiar estado a 'closed').
     */
    public function cerrar(Guard $guardia)
    {
        $this->authorize('cerrar', $guardia);

        // Verificar que la guardia esté abierta
        if ($guardia->status !== 'open') {
            return redirect()->route('admin.guardias.show', $guardia)
                ->with('error', 'La guardia ya está cerrada.');
        }

        // Actualizar estado y fecha de cierre
        $guardia->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        // Opcional: puedes registrar un log o notificación aquí

        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', 'Guardia cerrada correctamente.');
    }

    public function pdf(Guard $guardia)
    {
        $guardia->load([
            'capitan',
            'oficial',
            'escribiente',
            'salidasVehiculos.vehiculo',
            'salidasVehiculos.conductor',
        ]);

        $pdf = Pdf::loadView('admin.guardias.pdf.novedades', compact('guardia'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('novedades-' . $guardia->date->format('d-m-Y') . '.pdf');
    }
}
