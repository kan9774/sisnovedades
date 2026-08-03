<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HistorialEstadoPanel extends Component
{
    public ?User $user = null;

    public string $fecha = '';
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
        return $this->user->historialEstados()->get();
    }

    /**
     * El tipo del próximo movimiento se infiere solo, no se elige: si
     * está activo, lo único que tiene sentido es dar de baja; si está
     * de baja, lo único que tiene sentido es reingresarlo (si le quedan
     * altas disponibles).
     */
    #[Computed]
    public function proximoTipo(): string
    {
        return $this->user->estaActivoEnElEjercito() ? 'baja' : 'alta';
    }

    #[Computed]
    public function altasRestantes(): int
    {
        return $this->user->altasRestantes();
    }

    public function abrirForm(): void
    {
        abort_unless($this->puedeEditar(), 403);

        if ($this->proximoTipo === 'alta' && $this->altasRestantes === 0) {
            return;
        }

        $this->resetValidation();
        $this->fecha = now()->toDateString();
        $this->motivo = '';

        $this->dispatch('abrir-modal-historial-estado');
    }

    public function cerrarModal(): void
    {
        // El form no guarda estado propio más allá de fecha/motivo,
        // así que no hace falta resetear nada más acá.
    }

    public function guardar(): void
    {
        abort_unless($this->puedeEditar(), 403);

        $data = $this->validate([
            'fecha' => ['required', 'date'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->user->historialEstados()->create([
                'tipo' => $this->proximoTipo,
                'fecha' => $data['fecha'],
                'motivo' => $data['motivo'],
            ]);
        } catch (ValidationException $e) {
            $this->addError('tipo', collect($e->errors())->flatten()->first());
            return;
        }

        $this->user->refresh();
        unset($this->historial, $this->proximoTipo, $this->altasRestantes);

        $this->dispatch('cerrar-modal-historial-estado');

        session()->flash('success', 'Movimiento registrado correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.historial-estado-panel');
    }
}