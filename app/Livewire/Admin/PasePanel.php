<?php

namespace App\Livewire\Admin;

use App\Models\Pase;
use App\Models\Unidad;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PasePanel extends Component
{
    public ?User $user = null;

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
        return $this->user->pases()->with('unidad')->get();
    }

    #[Computed]
    public function unidades()
    {
        return Unidad::where('activo', true)->orderBy('nombre')->get();
    }

    public function abrirForm(): void
    {
        abort_unless($this->puedeEditar(), 403);

        $this->resetValidation();
        $this->reset(['fecha', 'unidad_id', 'numero_orden', 'motivo']);
        $this->fecha = now()->toDateString();

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

        $this->user->pases()->create([
            'unidad_id' => $data['unidad_id'],
            'fecha_desde' => Pase::fechaDesdeParaPase($data['fecha']),
            'numero_orden' => $data['numero_orden'],
            'motivo' => $data['motivo'],
        ]);

        $this->user->refresh();
        unset($this->historial);

        $this->dispatch('cerrar-modal-pase');

        session()->flash('success', 'Pase registrado correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.pase-panel');
    }
}