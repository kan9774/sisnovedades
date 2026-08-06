<?php

namespace App\Livewire\Admin;

use App\Models\Comision;
use App\Models\Unidad;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ComisionPanel extends Component
{
    public ?User $user = null;

    // Form: iniciar / editar comisión
    public ?int $comision_id = null;
    public bool $editando_historica = false;
    public string $fecha_inicio = '';
    public string $unidad_id = '';
    public string $tipo_orden = '';
    public string $numero_orden = '';
    public string $motivo = '';
    public string $edit_fecha_fin = '';

    // Form: finalizar comisión vigente
    public string $fecha_fin = '';

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

    /**
     * Abre el panel. Sin $id: modo alta. Con $id: modo edición. Si la
     * comisión editada ya está finalizada (tiene fecha_fin), habilita
     * también el campo de fecha de fin en el mismo formulario; si está
     * vigente, ese campo no se muestra (para cerrarla se usa el flujo
     * separado "Finalizar Comisión").
     */
    public function abrirForm(?int $id = null): void
    {
        abort_unless($this->puedeEditar(), 403);

        $this->resetValidation();
        $this->reset(['fecha_inicio', 'unidad_id', 'tipo_orden', 'numero_orden', 'motivo', 'comision_id', 'editando_historica', 'edit_fecha_fin']);

        if ($id) {
            $comision = $this->user->comisiones()->findOrFail($id);

            $this->comision_id = $comision->id;
            $this->editando_historica = !is_null($comision->fecha_fin);
            $this->fecha_inicio = $comision->fecha_inicio->toDateString();
            $this->unidad_id = (string) $comision->unidad_id;
            $this->tipo_orden = $comision->tipo_orden ?? '';
            $this->numero_orden = $comision->numero_orden ?? '';
            $this->motivo = $comision->motivo ?? '';
            $this->edit_fecha_fin = $comision->fecha_fin?->toDateString() ?? '';
        } else {
            $this->fecha_inicio = now()->toDateString();
        }

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

        $reglaUnidad = function (string $attribute, mixed $value, \Closure $fail) {
            // Un militar no puede estar en comisión en su propia unidad
            // formal: la comisión es, por definición, desempeño operativo
            // en OTRA unidad. Aplica igual en alta y en edición.
            if ((int) $value === (int) $this->user->unidad_id) {
                $fail('No se puede registrar una comisión en la misma unidad a la que el usuario pertenece.');
            }
        };

        $rules = [
            'fecha_inicio' => ['required', 'date'],
            'unidad_id' => ['required', 'exists:unidades,id', $reglaUnidad],
            'tipo_orden' => ['required', 'string', 'in:' . implode(',', Comision::TIPOS_ORDEN)],
            'numero_orden' => ['required', 'string', 'max:50'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ];

        // Solo si estamos editando una comisión ya finalizada se valida
        // (y se permite tocar) su fecha de cierre. Una comisión vigente
        // no puede "cerrarse" desde acá: para eso está finalizar().
        if ($this->editando_historica) {
            $rules['edit_fecha_fin'] = ['required', 'date', 'after_or_equal:fecha_inicio'];
        }

        $data = $this->validate($rules);

        if ($this->comision_id) {
            $this->actualizar($data);
        } else {
            $this->crear($data);
        }

        $this->user->refresh();
        unset($this->historial);

        $this->dispatch('cerrar-modal-comision-abrir');

        session()->flash('success', $this->comision_id
            ? 'Comisión actualizada correctamente.'
            : 'Comisión registrada correctamente.');
    }

    private function crear(array $data): void
    {
        $this->user->comisiones()->create([
            'unidad_id' => $data['unidad_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'tipo_orden' => $data['tipo_orden'],
            'numero_orden' => $data['numero_orden'],
            'motivo' => $data['motivo'],
        ]);
    }

    private function actualizar(array $data): void
    {
        $comision = $this->user->comisiones()->findOrFail($this->comision_id);

        $update = [
            'unidad_id' => $data['unidad_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'tipo_orden' => $data['tipo_orden'],
            'numero_orden' => $data['numero_orden'],
            'motivo' => $data['motivo'],
        ];

        // Solo se toca fecha_fin si ya era una comisión finalizada al
        // abrir el formulario (editando_historica). Si es la vigente,
        // fecha_fin queda intacta (null) — no se reabre ni se cierra
        // desde acá.
        if ($this->editando_historica) {
            $update['fecha_fin'] = $data['edit_fecha_fin'];
        }

        $comision->update($update);
    }

    /**
     * A diferencia de Pase, Comision no sincroniza nada en users (es
     * informativa a propósito), así que eliminar no tiene restricciones
     * de "el usuario queda en un estado inválido": se puede borrar
     * cualquier fila, vigente o histórica, sin efectos colaterales.
     */
    public function eliminar(int $id): void
    {
        abort_unless($this->puedeEditar(), 403);

        $comision = $this->user->comisiones()->findOrFail($id);
        $comision->delete();

        $this->user->refresh();
        unset($this->historial);

        session()->flash('success', 'Comisión eliminada correctamente.');
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