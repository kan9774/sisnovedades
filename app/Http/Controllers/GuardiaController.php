<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Support\GuardiaPdfGenerator;
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
            ->orderByDesc('date')
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
        $capitanes = User::whereHas('rol', fn($q) => $q->where('name', 'capitan_de_servicio'))
            ->get()
            ->reject(fn($u) => $u->isSuperAdmin())
            ->values();
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

    /**
     * Show the form for editing the resource.
     */
    public function edit(Guard $guardia)
    {
        $this->authorize('update', $guardia);

        $capitanes = User::whereHas('rol', fn($q) => $q->where('name', 'capitan_de_servicio'))
            ->get()
            ->reject(fn($u) => $u->isSuperAdmin())
            ->values();
        $oficiales = User::whereHas('rol', fn($q) => $q->where('name', 'oficial_de_dia'))->get();
        $escribientes = User::whereHas('rol', fn($q) => $q->where('name', 'escribiente'))->get();

        return view('admin.guardias.edit', compact('guardia', 'capitanes', 'oficiales', 'escribientes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Guard $guardia)
    {
        $this->authorize('update', $guardia);

        $data = $request->validate([
            'captain_id' => 'required|exists:users,id',
            'oficer_id' => 'required|exists:users,id',
            'escribiente_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Guard::getActivitylogOptions() (logFillable + logOnlyDirty) ya deja registrado
        // automáticamente, con el usuario autenticado como causante, cualquier cambio en
        // captain_id, oficer_id o notes. El escribiente es una relación (pivot), así que
        // ese cambio se audita a mano acá.
        $escribientesAnterioresIds = $guardia->escribiente()->pluck('users.id')->all();
        $escribienteNuevoId = (int) $data['escribiente_id'];

        $guardia->update([
            'captain_id' => $data['captain_id'],
            'oficer_id' => $data['oficer_id'],
            'notes' => $data['notes'] ?? null,
        ]);

        if ($escribientesAnterioresIds !== [$escribienteNuevoId]) {
            $guardia->escribiente()->sync([$escribienteNuevoId]);

            $nombresAnteriores = User::whereIn('id', $escribientesAnterioresIds)
                ->get()
                ->map(fn($u) => "{$u->grade} {$u->name} {$u->last_name}")
                ->implode(', ') ?: '—';
            $escribienteNuevo = User::find($escribienteNuevoId);
            $nombreNuevoStr = $escribienteNuevo
                ? "{$escribienteNuevo->grade} {$escribienteNuevo->name} {$escribienteNuevo->last_name}"
                : '—';

            activity('Guardias')
                ->causedBy(Auth::user())
                ->performedOn($guardia)
                ->withProperties([
                    'old' => ['escribiente' => $nombresAnteriores],
                    'attributes' => ['escribiente' => $nombreNuevoStr],
                ])
                ->log('Cambio de escribiente de la guardia');
        }

        return redirect()->route('admin.guardias.show', $guardia)->with('success', 'Guardia actualizada correctamente.');
    }

    public function show(Guard $guardia)
    {
        $guardia->load(['capitan', 'oficial', 'escribiente', 'ranchoMenu']);

        $unidadesActivas = \App\Models\Unidad::where('activo', true)->orderBy('nombre')->get();
        $rancho = $guardia->novedadesRancho->keyBy('unidad_id');

        return view('admin.guardias.show', compact(
            'guardia',
            'unidadesActivas',
            'rancho'
        ));
    }

    public function Hoy()
    {
        $guardia = Guard::Hoy()->first();
        if (!$guardia) {
            return redirect()->route('admin.guardias.index')->with('error', 'No hay guardia para hoy');
        }
        return redirect()->route('admin.guardias.show', $guardia);
    }

    public function reactivar(Guard $guardia)
    {
        $this->authorize('reactivar', $guardia);
        $guardia->disableLogging();
        $guardia->update([
            'status' => 'open',
            'closed_at' => null,
        ]);
        $guardia->enableLogging();
        activity('Guardias')
            ->causedBy(Auth::user())
            ->performedOn($guardia)
            ->log('Reactivó la guardia');
        return redirect()->route('admin.guardias.show', $guardia)->with('success', 'Guardia reactivada exitosamente');
    }

    /**
     * Cerrar una guardia (cambiar estado a 'closed').
     */
    public function cerrar(Guard $guardia)
    {
        $this->authorize('cerrar', $guardia);

        if ($guardia->status !== 'open') {
            return redirect()->route('admin.guardias.show', $guardia)
                ->with('error', 'La guardia ya está cerrada.');
        }

        $pendientes = $guardia->novedades()->pendientes()->count();

        if ($pendientes > 0 && !request()->boolean('forzar')) {
            return redirect()->route('admin.guardias.show', $guardia)
                ->with('warning', "No se puede cerrar: quedan {$pendientes} novedad(es) sin resolver (Caso a resolver). Si querés cerrar de todas formas, confirmá el cierre forzado.")
                ->with('pendientes_cierre', $pendientes);
        }
        $forzado = $pendientes > 0;
        $guardia->disableLogging();
        $guardia->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
        $guardia->enableLogging();
        activity('Guardias')
            ->causedBy(Auth::user())
            ->performedOn($guardia)
            ->withProperties([
                'forzado' => $forzado,
                'novedades_pendientes' => $pendientes,
            ])
            ->log($forzado
                ? "Cerró la guardia de forma forzada con {$pendientes} novedad(es) sin resolver"
                : 'Cerró la guardia');

        return redirect()->route('admin.guardias.show', $guardia)
            ->with('success', $pendientes > 0
                ? 'Guardia cerrada con novedades sin resolver.'
                : 'Guardia cerrada correctamente.');
    }

    public function pdf(Guard $guardia)
    {
        $pdf = GuardiaPdfGenerator::generar($guardia);

        return $pdf->stream(GuardiaPdfGenerator::nombreArchivo($guardia));
    }
}