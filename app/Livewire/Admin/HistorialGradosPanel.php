<?php

namespace App\Livewire\Admin;

use App\Models\Grado;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HistorialGradosPanel extends Component
{
    public ?User $user = null;

    public ?int $editandoId = null;

    public ?int $grado_id = null;

    public ?string $fecha_cambio = null;

    public string $numero_orden = '';
    public string $resolucion = '';
    public string $observaciones = '';

    public function mount(?User $user = null): void
    {
        $this->user = $user;
    }

    /**
     * Mismo criterio que UserForm::puedeEditarDatosBasicos(): solo
     * admin o superadmin pueden tocar numero_orden/resolución/
     * observaciones de un cambio de grado ya registrado.
     */
    public function puedeEditar(): bool
    {
        return auth()->user()->isSuperAdmin()
            || auth()->user()->roles->contains('name', 'admin');
    }

    #[Computed]
    public function historial()
    {
        return $this->user->historialGrados()->with('grado')->get();
    }

    #[Computed]
    public function grados()
    {
        return Grado::orderBy('orden')->get();
    }

    public function abrirNuevo(): void
    {
        abort_unless($this->puedeEditar(), 403);

        $this->resetValidation();

        $this->editandoId = null;
        $this->grado_id = null;
        $this->fecha_cambio = now()->toDateString();
        $this->numero_orden = '';
        $this->resolucion = '';
        $this->observaciones = '';

        $this->dispatch('abrir-modal-historial-grado');
    }

    public function abrirEditar(int $id): void
    {
        abort_unless($this->puedeEditar(), 403);

        $this->resetValidation();

        $registro = $this->user->historialGrados()->findOrFail($id);

        $this->editandoId = $registro->id;
        $this->grado_id = $registro->grado_id;
        $this->numero_orden = (string) ($registro->numero_orden ?? '');
        $this->resolucion = $registro->resolucion ?? '';
        $this->observaciones = $registro->observaciones ?? '';

        $this->dispatch('abrir-modal-historial-grado');
    }

    public function cerrarModal(): void
    {
        $this->editandoId = null;
    }

    public function guardar(): void
    {
        abort_unless($this->puedeEditar(), 403);

        if ($this->editandoId === null) {
            $this->guardarNuevo();
            return;
        }

        $data = $this->validate([
            'grado_id' => ['required', 'exists:grados,id'],
            'numero_orden' => ['nullable', 'string', 'max:50', 'regex:/^\d{1,4}\/\d{4}$/'],
            'resolucion' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $registro = $this->user->historialGrados()->findOrFail($this->editandoId);

        // Si el registro editado es el más reciente del historial, es el que
        // define el grado actual del usuario (caché en users.grado_id) —
        // hay que mantenerlo sincronizado con el nuevo valor.
        $esElMasReciente = $this->user->historialGrados()
            ->orderByDesc('fecha_cambio')
            ->orderByDesc('id')
            ->value('id') === $registro->id;

        $registro->update($data);

        if ($esElMasReciente) {
            $this->user->update(['grado_id' => $registro->grado_id]);
        }

        $this->cerrarModal();
        $this->dispatch('cerrar-modal-historial-grado');

        session()->flash('success', 'Historial de grado actualizado correctamente.');
    }

    /**
     * Registra un cambio de grado nuevo (ascenso o degradación), en
     * reemplazo del hook que antes vivía en UserForm::save(). Calcula
     * `tipo` con el mismo criterio: en esta app `orden` va de mayor
     * jerarquía a menor, así que un ascenso es cuando el `orden` nuevo
     * es MENOR que el del grado anterior, y una degradación cuando es
     * MAYOR. El "grado anterior" es el del último movimiento del
     * historial (o el `grado_id` cacheado en `users` si todavía no
     * tiene ningún historial cargado).
     */
    private function guardarNuevo(): void
    {
        $data = $this->validate([
            'grado_id' => ['required', 'exists:grados,id'],
            'fecha_cambio' => ['required', 'date'],
            'numero_orden' => ['nullable', 'string', 'max:50', 'regex:/^\d{1,4}\/\d{4}$/'],
            'resolucion' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $gradoAnteriorId = $this->user->historialGrados()->first()?->grado_id
            ?? $this->user->grado_id;

        if ($gradoAnteriorId === (int) $data['grado_id']) {
            $this->addError('grado_id', 'Ese ya es el grado actual del usuario.');
            return;
        }

        $tipo = 'ascenso';

        if ($gradoAnteriorId) {
            $ordenAnterior = Grado::find($gradoAnteriorId)?->orden;
            $ordenNuevo = Grado::find($data['grado_id'])?->orden;

            if ($ordenAnterior !== null && $ordenNuevo !== null && $ordenNuevo > $ordenAnterior) {
                $tipo = 'degradacion';
            }
        }

        $registro = $this->user->historialGrados()->create([
            'grado_id' => $data['grado_id'],
            'tipo' => $tipo,
            'fecha_cambio' => $data['fecha_cambio'],
            'numero_orden' => $data['numero_orden'],
            'resolucion' => $data['resolucion'],
            'observaciones' => $data['observaciones'],
        ]);

        // El movimiento recién creado puede no ser el más reciente si se
        // cargó con una fecha atrasada (alta tardía) — solo sincronizamos
        // el grado actual del usuario si efectivamente lo es.
        $masReciente = $this->user->historialGrados()
            ->orderByDesc('fecha_cambio')
            ->orderByDesc('id')
            ->first();

        if ($masReciente?->id === $registro->id) {
            $this->user->update(['grado_id' => $registro->grado_id]);
        }

        $this->cerrarModal();
        $this->dispatch('cerrar-modal-historial-grado');

        session()->flash('success', 'Cambio de grado registrado correctamente.');
    }

    public function eliminar(int $id): void
    {
        abort_unless($this->puedeEditar(), 403);

        $registro = $this->user->historialGrados()->findOrFail($id);

        // Si el registro que se borra es el más reciente (el que define el
        // grado actual del usuario), el usuario vuelve a quedar con el grado
        // del movimiento anterior. Si no queda ninguno, no tocamos users.grado_id
        // porque significaría que el usuario se queda sin grado.
        $esElMasReciente = $this->user->historialGrados()
            ->orderByDesc('fecha_cambio')
            ->orderByDesc('id')
            ->value('id') === $registro->id;

        $registro->delete();

        if ($esElMasReciente) {
            $anterior = $this->user->historialGrados()
                ->orderByDesc('fecha_cambio')
                ->orderByDesc('id')
                ->first();

            if ($anterior) {
                $this->user->update(['grado_id' => $anterior->grado_id]);
            }
        }

        session()->flash('success', 'Movimiento de historial eliminado.');
    }

    public function render()
    {
        return view('livewire.admin.historial-grados-panel');
    }
}