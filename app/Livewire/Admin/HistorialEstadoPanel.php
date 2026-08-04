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
    public string $causal_id = '';
    public string $procedencia = '';

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
        $this->fecha = '';
        $this->motivo = '';
        $this->causal_id = '';
        $this->procedencia = '';

        $this->dispatch('abrir-modal-historial-estado');
    }

    public function cerrarModal(): void
    {
        // El form no guarda estado propio más allá de fecha/motivo,
        // así que no hace falta resetear nada más acá.
    }
    #[Computed]
    public function causales()
    {
        return \App\Models\CausalBaja::where('activo', true)->orderBy('nombre')->get();
    }

    public function guardar(): void
    {
        abort_unless($this->puedeEditar(), 403);

        if ($this->proximoTipo === 'baja') {
            $data = $this->validate([
                'fecha'     => ['required', 'date'],
                'causal_id' => ['required', 'exists:causales_baja,id'],
                'motivo'    => ['nullable', 'string', 'max:255'],
            ]);
        } else {
            // Reingreso: siempre 1° de un mes que todavía no arrancó.
            $primerMesValido = now()->startOfMonth()->addMonthNoOverflow();

            $data = $this->validate([
                'fecha' => [
                    'required',
                    'date',
                    'after_or_equal:' . $primerMesValido->toDateString(),
                    function ($attribute, $value, $fail) {
                        if (\Carbon\Carbon::parse($value)->day !== 1) {
                            $fail('La fecha de reingreso debe ser el 1° de un mes.');
                        }
                    },
                ],
                'procedencia' => ['required', 'string', 'max:255'],
            ]);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
                if ($this->proximoTipo === 'alta') {
                    $this->user->historialEstados()->create([
                        'tipo'   => 'alta',
                        'fecha'  => $data['fecha'],
                        'motivo' => null,
                    ]);

                    $unidadFija = \App\Models\Unidad::where('nombre', 'B.Com.N°1')->firstOrFail();

                    $this->user->pases()->create([
                        'unidad_id'   => $unidadFija->id,
                        'fecha_desde' => $data['fecha'],
                        'motivo'      => $data['procedencia'],
                    ]);
                } else {
                    $this->user->historialEstados()->create([
                        'tipo'      => 'baja',
                        'fecha'     => $data['fecha'],
                        'causal_id' => $data['causal_id'],
                        'motivo'    => $data['motivo'] ?? null,
                    ]);
                }
            });
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
