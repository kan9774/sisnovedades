<?php

namespace App\Livewire\Admin;

use App\Models\Comision;
use App\Models\Unidad;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ComisionPanel extends Component
{
    public User $user;

    // Form: iniciar comisión
    public string $fecha_inicio = '';
    public string $unidad_id = '';
    public string $tipo_orden = '';
    public string $numero_orden = '';
    public string $motivo = '';

    // Form: finalizar comisión vigente
    public string $fecha_fin = '';

    public function mount(User $user): void
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
        return $this->user->comisiones()->with('unidad')->latest('fecha_inicio')->get();
    }

    #[Computed]
    public function unidades()
    {
        return Unidad::where('activo', true)->orderBy('nombre')->get();
    }

    #[Computed]
    public function tiposOrden()
    {
        return Comision::TIPOS_ORDEN;
    }

    public function abrirForm(): void
    {
        abort_unless($this->puedeEditar(), 403);

        $this->resetValidation();
        $this->reset(['fecha_inicio', 'unidad_id', 'tipo_orden', 'numero_orden', 'motivo']);
        $this->fecha_inicio = now()->toDateString();

        $this->dispatch('abrir-modal-comision-abrir');
    }

    public function abrirFormCierre(): void
    {
        abort_unless($this->puedeEditar(), 403);

        $this->resetValidation();
        $this->reset(['fecha_fin']);
        $this->fecha_fin = now()->toDateString();

        $this->dispatch('abrir-modal-comision-cerrar');
    }

    public function guardar(): void
    {
        abort_unless($this->puedeEditar(), 403);

        $data = $this->validate([
            'fecha_inicio' => ['required', 'date'],
            'unidad_id' => [
                'required',
                'exists:unidades,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    // Un militar no puede estar en comisión en su propia
                    // unidad formal: la comisión es, por definición,
                    // desempeño operativo en OTRA unidad.
                    if ((int) $value === (int) $this->user->unidad_id) {
                        $fail('No se puede registrar una comisión en la misma unidad a la que el usuario pertenece.');
                    }
                },
            ],
            'tipo_orden' => ['required', 'string', 'in:' . implode(',', Comision::TIPOS_ORDEN)],
            'numero_orden' => ['required', 'string', 'max:50'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $this->user->comisiones()->create([
            'unidad_id' => $data['unidad_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'tipo_orden' => $data['tipo_orden'],
            'numero_orden' => $data['numero_orden'],
            'motivo' => $data['motivo'],
        ]);

        $this->user->refresh();
        unset($this->historial);

        $this->dispatch('cerrar-modal-comision-abrir');

        session()->flash('success', 'Comisión registrada correctamente.');
    }

    public function finalizar(): void
    {
        abort_unless($this->puedeEditar(), 403);

        $vigente = $this->user->comisionVigente();

        abort_unless($vigente, 404);

        $data = $this->validate([
            'fecha_fin' => ['required', 'date', 'after_or_equal:' . $vigente->fecha_inicio->toDateString()],
        ]);

        $vigente->update(['fecha_fin' => $data['fecha_fin']]);

        $this->user->refresh();
        unset($this->historial);

        $this->dispatch('cerrar-modal-comision-cerrar');

        session()->flash('success', 'Comisión finalizada correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.comision-panel');
    }
}