<?php

use App\Models\Guard;
use App\Models\News;
use App\Models\Organismo;
use App\Models\Attach;
use App\Models\User;
use App\Notifications\NovedadUrgenteNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    public Guard $guardia;
    public bool $puedeOperarGuardia = false;

    public ?int $editandoId = null;

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
    public $archivo = null;

    public function mount(Guard $guardia, bool $puedeOperarGuardia = false): void
    {
        $this->guardia = $guardia;
        $this->puedeOperarGuardia = $puedeOperarGuardia;
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

    #[Computed]
    public function novedades()
    {
        return $this->guardia->novedades()
            ->with('escribiente')
            ->orderBy('time')
            ->paginate(15);
    }

    public function abrirCrear(): void
    {
        $this->resetValidation();
        $this->reset([
            'editandoId', 'type', 'direction', 'destino', 'office_id', 'number',
            'time', 'affair', 'text', 'clasification', 'organismo_id', 'organismo_nuevo', 'archivo',
        ]);
        $this->time = now()->format('H:i');
        $this->dispatch('abrir-modal-novedad');
    }

    public function abrirEditar(int $id): void
    {
        $novedad = $this->guardia->novedades()->findOrFail($id);
        $this->authorize('update', $novedad);

        $this->resetValidation();
        $this->editandoId = $novedad->id;
        $this->type = $novedad->type;
        $this->direction = $novedad->direction;
        $this->destino = $novedad->destino ?? '';
        $this->office_id = (string) $novedad->office_id;
        $this->number = $novedad->number;
        $this->time = $novedad->time?->format('H:i') ?? '';
        $this->affair = $novedad->affair ?? '';
        $this->text = $novedad->text;
        $this->clasification = $novedad->clasification;
        $this->organismo_id = (string) ($novedad->organismo_id ?? '');
        $this->organismo_nuevo = '';
        $this->archivo = null;

        $this->dispatch('abrir-modal-novedad');
    }

    public function guardar(): void
    {
        abort_unless($this->puedeOperarGuardia && $this->guardia->status === 'open', 403);

        $rules = [
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
        ];

        if (!$this->editandoId) {
            $rules['archivo'] = [
                'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240',
                in_array($this->type, ['Fax', 'Correo Electrónico', 'Radio']) ? 'required' : 'nullable',
            ];
        }

        $data = $this->validate($rules);

        $organismoId = $data['organismo_id'] ?: null;
        if (filled($this->organismo_nuevo)) {
            $organismo = Organismo::firstOrCreate(['name' => $this->organismo_nuevo]);
            $organismoId = $organismo->id;
        }
        if ($data['direction'] === 'Expedido') {
            $organismoId = null;
        }

        $payload = [
            'type'            => $data['type'],
            'direction'       => $data['direction'],
            'destino'         => $data['destino'] ?: null,
            'office_id'       => $data['office_id'],
            'number'          => $data['number'],
            'time'            => $data['time'],
            'affair'          => $data['affair'] ?: null,
            'text'            => $data['text'],
            'clasification'   => $data['clasification'],
            'organismo_id'    => $organismoId,
        ];

        if ($this->editandoId) {
            $novedad = $this->guardia->novedades()->findOrFail($this->editandoId);
            $this->authorize('update', $novedad);
            $novedad->update($payload);
        } else {
            $this->authorize('create', [News::class, $this->guardia]);

            $novedad = $this->guardia->novedades()->create([
                ...$payload,
                'user_id'         => Auth::id(),
                'estado_atencion' => $data['direction'] === 'Recibido' ? 'pendiente' : null,
            ]);

            if ($this->archivo) {
                $fecha      = $this->guardia->date->format('dmY');
                $carpeta    = $data['direction'] === 'Recibido' ? 'Recibidos' : 'Expedidos';
                $directorio = "{$fecha}/{$carpeta}";
                $nombre     = time() . '_' . $this->archivo->getClientOriginalName();
                $ruta       = $this->archivo->storeAs($directorio, $nombre, 'guardias');

                Attach::create([
                    'news_id'   => $novedad->id,
                    'user_id'   => Auth::id(),
                    'file_name' => $this->archivo->getClientOriginalName(),
                    'file_path' => $ruta,
                    'file_type' => $this->archivo->getMimeType(),
                    'file_size' => $this->archivo->getSize(),
                ]);
            }

            if ($novedad->office_id && $novedad->direction === 'Recibido') {
                $destinatarios = User::where('oficina_id', $novedad->office_id)
                    ->where('id', '!=', Auth::id())
                    ->get();

                if ($destinatarios->isNotEmpty()) {
                    Notification::send($destinatarios, new NovedadUrgenteNotification($novedad));
                }
            }
        }

        unset($this->novedades);
        $this->dispatch('cerrar-modal-novedad');
    }

    public function eliminar(int $id): void
    {
        $novedad = $this->guardia->novedades()->findOrFail($id);
        $this->authorize('delete', $novedad);

        $novedad->delete();
        unset($this->novedades);
    }
};