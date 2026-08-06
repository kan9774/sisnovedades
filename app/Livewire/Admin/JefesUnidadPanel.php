<?php

namespace App\Livewire\Admin;

use App\Models\Grado;
use App\Models\JefeUnidad;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class JefesUnidadPanel extends Component
{
    public ?int $editandoId = null;

    public string $nombre_completo = '';
    public ?int $grado_id = null;
    public string $cargo = '';
    public string $fecha_desde = '';
    public ?string $fecha_hasta = null;

    public function mount(): void
    {
        $this->fecha_desde = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'nombre_completo' => ['required', 'string', 'max:255'],
            'grado_id'        => ['required', 'exists:grados,id'],
            'cargo'           => ['required', 'string', 'max:255'],
            'fecha_desde'     => ['required', 'date'],
            'fecha_hasta'     => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ];
    }

    #[Computed]
    public function jefes(): Collection
    {
        return JefeUnidad::with('grado')
            ->orderByDesc('fecha_desde')
            ->get();
    }

    #[Computed]
    public function vigenteId(): ?int
    {
        return JefeUnidad::vigente()?->id;
    }

    public function editar(int $id): void
    {
        $jefe = JefeUnidad::findOrFail($id);

        $this->editandoId = $jefe->id;
        $this->nombre_completo = $jefe->nombre_completo;
        $this->grado_id = $jefe->grado_id;
        $this->cargo = $jefe->cargo;
        $this->fecha_desde = $jefe->fecha_desde->toDateString();
        $this->fecha_hasta = $jefe->fecha_hasta?->toDateString();

        $this->resetErrorBag();
    }

    public function cancelarEdicion(): void
    {
        $this->reset(['editandoId', 'nombre_completo', 'grado_id', 'cargo', 'fecha_hasta']);
        $this->fecha_desde = now()->toDateString();
        $this->resetErrorBag();
    }

    public function guardar(): void
    {
        $validado = $this->validate();

        if ($this->editandoId) {
            JefeUnidad::findOrFail($this->editandoId)->update($validado);
            session()->flash('success', 'Jefe de Unidad actualizado.');
        } else {
            // El método booted() del modelo cierra automáticamente al vigente anterior.
            JefeUnidad::create($validado);
            session()->flash('success', 'Jefe de Unidad agregado.');
        }

        $this->cancelarEdicion();
        unset($this->jefes, $this->vigenteId);
    }

    public function eliminar(int $id): void
    {
        JefeUnidad::findOrFail($id)->delete();

        if ($this->editandoId === $id) {
            $this->cancelarEdicion();
        }

        unset($this->jefes, $this->vigenteId);
        session()->flash('success', 'Jefe de Unidad eliminado.');
    }

    public function render()
    {
        return view('livewire.admin.jefes-unidad.jefes-unidad-panel', [
            'grados' => Grado::orderBy('nombre')->get(),
        ]);
    }
}