<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HistorialGradosPanel extends Component
{
    public ?User $user = null;

    public ?int $editandoId = null;

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

    public function abrirEditar(int $id): void
    {
        abort_unless($this->puedeEditar(), 403);

        $this->resetValidation();

        $registro = $this->user->historialGrados()->findOrFail($id);

        $this->editandoId = $registro->id;
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

        $data = $this->validate([
            'numero_orden' => ['nullable', 'string', 'max:50', 'regex:/^\d{1,4}\/\d{4}$/'],
            'resolucion' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $registro = $this->user->historialGrados()->findOrFail($this->editandoId);
        $registro->update($data);

        unset($this->historial);

        $this->cerrarModal();
        $this->dispatch('cerrar-modal-historial-grado');

        session()->flash('success', 'Historial de grado actualizado correctamente.');
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

        unset($this->historial);

        session()->flash('success', 'Movimiento de historial eliminado.');
    }

    public function render()
    {
        return view('livewire.admin.historial-grados-panel');
    }
}
