<?php

namespace App\Livewire\Admin;

use App\Models\Pase;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PasePanel extends Component
{
    public ?User $user = null;

    public ?int $pase_id = null;

    public string $fecha = '';
    public string $unidad_id = '';
    public string $numero_orden = '';
    public string $motivo = '';

    public function mount(?User $user = null): void
    {
        $this->user = $user;
    }

    public function puedeEditar(): bool
    {
        return auth()->user()->isSuperAdmin()
            || auth()->user()->roles->contains('name', 'admin');
    }

    #[Computed]
    public function historial()
    {
        return $this->user->pases()->with('unidad')->orderByDesc('fecha_desde')->get();
    }

    #[Computed]
    public function unidades()
    {
        return Unidad::where('activo', true)->orderBy('nombre')->get();
    }

    /**
     * Abre el panel. Sin $id: modo alta. Con $id: modo edición, precarga
     * los datos del pase (la fecha se muestra pero queda de solo lectura
     * en el blade — ver comentario en guardar()).
     */
    public function abrirForm(?int $id = null): void
    {
        abort_unless($this->puedeEditar(), 403);

        $this->resetValidation();
        $this->reset(['fecha', 'unidad_id', 'numero_orden', 'motivo', 'pase_id']);

        if ($id) {
            $pase = $this->user->pases()->findOrFail($id);

            $this->pase_id = $pase->id;
            $this->fecha = $pase->fecha_desde->toDateString();
            $this->unidad_id = (string) $pase->unidad_id;
            $this->numero_orden = $pase->numero_orden ?? '';
            $this->motivo = $pase->motivo ?? '';
        } else {
            $this->fecha = now()->toDateString();
        }

        $this->dispatch('abrir-modal-pase');
    }

    public function cerrarModal(): void
    {
        //
    }

    public function guardar(): void
    {
        abort_unless($this->puedeEditar(), 403);

        $data = $this->validate([
            'fecha' => ['required', 'date'],
            'unidad_id' => ['required', 'exists:unidades,id'],
            'numero_orden' => ['required', 'string', 'max:50'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->pase_id) {
            $this->actualizar($data);
        } else {
            $this->crear($data);
        }

        $this->user->refresh();

        $this->dispatch('cerrar-modal-pase');

        session()->flash('success', $this->pase_id
            ? 'Pase actualizado correctamente.'
            : 'Pase registrado correctamente.');
    }

    private function crear(array $data): void
    {
        // No tiene sentido pasar a la misma unidad donde el usuario ya
        // presta servicios.
        if ((int) $data['unidad_id'] === (int) $this->user->unidad_id) {
            throw ValidationException::withMessages([
                'unidad_id' => 'El usuario ya presta servicios en esa unidad. No tiene sentido registrar un pase a la misma unidad.',
            ]);
        }

        $this->user->pases()->create([
            'unidad_id' => $data['unidad_id'],
            'fecha_desde' => Pase::fechaDesdeParaPase($data['fecha']),
            'numero_orden' => $data['numero_orden'],
            'motivo' => $data['motivo'],
        ]);
    }

    /**
     * Solo corrige unidad_id, numero_orden y motivo — la fecha no se toca
     * acá (ver blade: el campo queda deshabilitado en modo edición). El
     * hook automático de Pase no corre en updates, así que si el pase
     * editado es el vigente, hay que resincronizar users.unidad_id a mano.
     */
    private function actualizar(array $data): void
    {
        $pase = $this->user->pases()->findOrFail($this->pase_id);

        $esVigente = is_null($pase->fecha_hasta);

        // Si es el vigente, comparar contra la unidad actual del usuario
        // no tiene sentido (es el mismo registro). Si es un pase histórico,
        // tampoco corresponde esta regla: no está reasignando nada en vivo.
        $pase->update([
            'unidad_id' => $data['unidad_id'],
            'numero_orden' => $data['numero_orden'],
            'motivo' => $data['motivo'],
        ]);

        if ($esVigente) {
            $this->user->update(['unidad_id' => $data['unidad_id']]);
        }
    }

    /**
     * Elimina un pase, cuidando que el usuario nunca quede sin unidad
     * asignada:
     * - Si es el único pase del usuario, se bloquea.
     * - Si es el pase vigente y hay uno anterior, se reabre el anterior
     *   (fecha_hasta = null) y se resincroniza users.unidad_id antes de
     *   borrar.
     * - Si es un pase histórico (ya cerrado), se borra directo: no afecta
     *   la unidad vigente del usuario.
     */
    public function eliminar(int $id): void
    {
        abort_unless($this->puedeEditar(), 403);

        $pase = $this->user->pases()->findOrFail($id);

        if ($this->user->pases()->count() <= 1) {
            session()->flash('error', 'No se puede eliminar: es el único pase del usuario y quedaría sin unidad asignada.');
            return;
        }

        if (is_null($pase->fecha_hasta)) {
            $anterior = $this->user->pases()
                ->where('id', '!=', $pase->id)
                ->latest('fecha_desde')
                ->first();

            if (!$anterior) {
                session()->flash('error', 'No se puede eliminar: el usuario quedaría sin unidad asignada.');
                return;
            }

            $anterior->update(['fecha_hasta' => null]);
            $this->user->update(['unidad_id' => $anterior->unidad_id]);
        }

        $pase->delete();

        $this->user->refresh();

        session()->flash('success', 'Pase eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.pase-panel');
    }
}