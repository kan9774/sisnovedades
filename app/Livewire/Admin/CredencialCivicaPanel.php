<?php

namespace App\Livewire\Admin;

use App\Models\CredencialCivica;
use App\Models\Departamento;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CredencialCivicaPanel extends Component
{
    public User $user;

    public ?int $editandoId = null;

    public ?int $departamento_id = null;
    public string $serie = '';
    public string $numero = '';
    public string $fecha_desde = '';
    public ?string $fecha_hasta = null;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->fecha_desde = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'departamento_id' => ['required', 'exists:departamentos,id'],
            'serie'           => ['required', 'string', 'max:255'],
            'numero'          => ['required', 'string', 'max:255'],
            'fecha_desde'     => ['required', 'date'],
            'fecha_hasta'     => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ];
    }

    #[Computed]
    public function credenciales(): Collection
    {
        return CredencialCivica::with('departamento')
            ->where('user_id', $this->user->id)
            ->orderByDesc('fecha_desde')
            ->get();
    }

    public function editar(int $id): void
    {
        $credencial = CredencialCivica::where('user_id', $this->user->id)->findOrFail($id);

        $this->editandoId = $credencial->id;
        $this->departamento_id = $credencial->departamento_id;
        $this->serie = $credencial->serie;
        $this->numero = $credencial->numero;
        $this->fecha_desde = $credencial->fecha_desde->toDateString();
        $this->fecha_hasta = $credencial->fecha_hasta?->toDateString();

        $this->resetErrorBag();
    }

    public function cancelarEdicion(): void
    {
        $this->reset(['editandoId', 'departamento_id', 'serie', 'numero', 'fecha_hasta']);
        $this->fecha_desde = now()->toDateString();
        $this->resetErrorBag();
    }

    public function guardar(): void
    {
        $validado = $this->validate();

        if ($this->editandoId) {
            CredencialCivica::where('user_id', $this->user->id)
                ->findOrFail($this->editandoId)
                ->update($validado);

            // El update no dispara created(), así que sincronizamos el caché a mano
            // si se está editando la que está vigente (fecha_hasta null).
            if (is_null($validado['fecha_hasta'])) {
                $this->user->update([
                    'credencial_departamento_id' => $validado['departamento_id'],
                    'credencial_serie' => $validado['serie'],
                    'credencial_numero' => $validado['numero'],
                ]);
            }

            session()->flash('success', 'Credencial cívica actualizada.');
        } else {
            // booted() del modelo cierra la anterior vigente y sincroniza el caché en users.
            CredencialCivica::create([...$validado, 'user_id' => $this->user->id]);
            session()->flash('success', 'Credencial cívica agregada.');
        }

        $this->cancelarEdicion();
    }

    public function eliminar(int $id): void
    {
        CredencialCivica::where('user_id', $this->user->id)->findOrFail($id)->delete();

        if ($this->editandoId === $id) {
            $this->cancelarEdicion();
        }
        session()->flash('success', 'Credencial cívica eliminada.');
    }

    public function render()
    {
        return view('livewire.admin.credencial-civica-panel', [
            'departamentos' => Departamento::orderBy('nombre')->get(),
        ]);
    }
}