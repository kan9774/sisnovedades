<?php

namespace App\Livewire;

use App\Models\Guard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class GuardiaAcciones extends Component
{
    public Guard $guardia;

    // Feedback
    public $successMsg = '';
    public $errorMsg = '';

    public function mount(Guard $guardia)
    {
        $this->guardia = $guardia;

        try {
            $this->authorize('view', $this->guardia);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
        }
    }

    public function cerrar()
    {
        try {
            $this->authorize('cerrar', $this->guardia);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        if ($this->guardia->status !== 'open') {
            $this->errorMsg = 'La guardia ya está cerrada.';
            return;
        }

        $pendientes = $this->guardia->novedades()->pendientes()->count();

        if ($pendientes > 0) {
            $this->dispatch('mostrarAlertaCerrar', $pendientes);
        } else {
            $this->cerrarForzado();
        }
    }

    public function cerrarForzado()
    {
        try {
            $this->authorize('cerrar', $this->guardia);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        if ($this->guardia->status !== 'open') {
            $this->errorMsg = 'La guardia ya está cerrada.';
            return;
        }

        $pendientes = $this->guardia->novedades()->pendientes()->count();
        $forzado = $pendientes > 0;

        $this->guardia->disableLogging();
        $this->guardia->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
        $this->guardia->enableLogging();

        activity('Guardias')
            ->causedBy(Auth::user())
            ->performedOn($this->guardia)
            ->withProperties([
                'forzado' => $forzado,
                'novedades_pendientes' => $pendientes,
            ])
            ->log($forzado
                ? "Cerró la guardia de forma forzada con {$pendientes} novedad(es) sin resolver"
                : 'Cerró la guardia');

        $this->successMsg = $forzado
            ? 'Guardia cerrada con novedades sin resolver.'
            : 'Guardia cerrada correctamente.';

        $this->dispatch('guardiaUpdated');
    }

    public function ejecutarReactivar()
    {
        try {
            $this->authorize('reactivar', $this->guardia);
        } catch (AuthorizationException $e) {
            $this->errorMsg = $e->getMessage();
            return;
        }

        $this->guardia->disableLogging();
        $this->guardia->update([
            'status' => 'open',
            'closed_at' => null,
        ]);
        $this->guardia->enableLogging();

        activity('Guardias')
            ->causedBy(Auth::user())
            ->performedOn($this->guardia)
            ->log('Reactivó la guardia');

        $this->successMsg = 'Guardia reactivada exitosamente.';

        $this->dispatch('guardiaUpdated');
    }

    #[On('cerrarForzadoDesdeAlerta')]
    public function cerrarForzadoDesdeAlerta()
    {
        $this->cerrarForzado();
    }

    public function render()
    {
        return view('livewire.guardia-acciones.index');
    }
}
