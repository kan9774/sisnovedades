<?php

namespace App\Livewire;

use App\Models\Guard;
use App\Models\News;
use App\Models\Organismo;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EditarNovedadModal extends Component
{
    public News $novedad;
    public Guard $guardia;

    public string $type = '';
    public string $direction = '';
    public string $destino = '';
    public string $office_id = '';
    public string $number = '';
    public string $time = '';
    public string $affair = '';
    public string $text = '';
    public string $clasification = '';
    public string $organismo_id = '';
    public string $organismo_nuevo = '';

    public function mount(News $novedad, Guard $guardia): void
    {
        $this->novedad = $novedad;
        $this->guardia = $guardia;

        $this->cargarDatos();
    }

    private function cargarDatos(): void
    {
        $this->type = $this->novedad->type;
        $this->direction = $this->novedad->direction;
        $this->destino = $this->novedad->destino ?? '';
        $this->office_id = (string) $this->novedad->office_id;
        $this->number = $this->novedad->number;
        $this->time = $this->novedad->time?->format('H:i') ?? '';
        $this->affair = $this->novedad->affair ?? '';
        $this->text = $this->novedad->text;
        $this->clasification = $this->novedad->clasification;
        $this->organismo_id = (string) ($this->novedad->organismo_id ?? '');
        $this->organismo_nuevo = '';
    }

    #[Computed]
    public function organismos()
    {
        return Organismo::orderBy('name')->get();
    }

    #[Computed]
    public function oficinas()
    {
        return \App\Models\Oficina::where('activo', true)->orderBy('nombre')->get();
    }

    public function abrir(): void
    {
        $this->authorize('update', $this->novedad);
        $this->resetValidation();
        $this->cargarDatos();
        $this->dispatch('abrir-modal-editar-novedad');
    }

    public function guardar()
    {
        $this->authorize('update', $this->novedad);

        $data = $this->validate([
            'type'            => 'required|in:Radio,Fax,Correo Electrónico',
            'direction'       => 'required|in:Recibido,Expedido',
            'number'          => 'required|string|max:255',
            'time'            => 'required|date_format:H:i',
            'office_id'       => 'required|exists:oficinas,id',
            'affair'          => 'nullable|string|max:255',
            'text'            => 'required|string',
            'destino'         => 'nullable|string|max:255',
            'clasification'   => 'required|in:Rutinario,Prioritario,Urgente,Destello',
            'organismo_id'    => 'nullable|exists:organismos,id',
            'organismo_nuevo' => 'nullable|string|max:255',
        ]);

        $organismoId = $data['organismo_id'] ?: null;
        if (filled($this->organismo_nuevo)) {
            $organismo = Organismo::firstOrCreate(['name' => $this->organismo_nuevo]);
            $organismoId = $organismo->id;
        }
        if ($data['direction'] === 'Expedido') {
            $organismoId = null;
        }

        $payload = [
            'type'          => $data['type'],
            'direction'     => $data['direction'],
            'destino'       => $data['destino'] ?: null,
            'office_id'     => $data['office_id'],
            'number'        => $data['number'],
            'time'          => $data['time'],
            'affair'        => $data['affair'] ?: null,
            'text'          => $data['text'],
            'clasification' => $data['clasification'],
            'organismo_id'  => $organismoId,
        ];

        $this->novedad->update($payload);

        // La mayoría de los campos se muestran fuera de este componente
        // (en el blade padre), así que refrescamos la página vía navigate
        // para reflejarlos sin perder la sensación de SPA.
        return $this->redirect(
            route('admin.guardias.novedades.show', [$this->guardia, $this->novedad]),
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.editar-novedad-modal');
    }
}