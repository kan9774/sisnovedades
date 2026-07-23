<?php

use App\Models\Guard;
use App\Models\News;
use App\Models\Organismo;
use App\Models\Attach;
use App\Models\User;
use App\Notifications\NovedadUrgenteNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

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
    public array $archivos = [];

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
    public function novedadesAgrupadas()
    {
        // Sin paginación a propósito: agrupar por dirección/tipo/hora solo
        // tiene sentido viendo el conjunto completo de la guardia (un día),
        // no fragmentado en páginas de 8. El volumen de una guardia no
        // amerita paginar.
        $todas = $this->guardia->novedades()
            ->with(['oficina', 'tomadoPor'])
            ->orderBy('time')
            ->get();

        // Orden fijo de subgrupos dentro de cada dirección, para que
        // Radio siempre aparezca antes que Correo Electrónico y Fax,
        // sin importar cuál tenga más registros.
        $ordenTipos = ['Radio' => 0, 'Correo Electrónico' => 1, 'Fax' => 2];

        return $todas
            ->groupBy('direction')
            ->map(function ($porDireccion) use ($ordenTipos) {
                return $porDireccion
                    ->groupBy('type')
                    ->sortBy(fn ($grupo, $tipo) => $ordenTipos[$tipo] ?? 99)
                    ->map(fn ($grupo) => $grupo->sortBy('time')->values());
            });
    }

    public function abrirCrear(): void
    {
        $this->resetValidation();
        $this->reset([
            'editandoId',
            'type',
            'direction',
            'destino',
            'office_id',
            'number',
            'time',
            'affair',
            'text',
            'clasification',
            'organismo_id',
            'organismo_nuevo',
            'archivos',
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
        $this->archivos = [];

        $this->dispatch('abrir-modal-novedad');
    }

    public function quitarArchivo(int $index): void
    {
        unset($this->archivos[$index]);
        $this->archivos = array_values($this->archivos);
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
            $rules['archivos'] = ['nullable', 'array', 'max:5'];
            $rules['archivos.*'] = ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'];
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

            $oficinaAnterior   = $novedad->office_id;
            $direccionAnterior = $novedad->direction;

            $cambioOficina   = $data['direction'] === 'Recibido'
                && (string) $oficinaAnterior !== (string) $data['office_id'];
            $pasaARecibido   = $data['direction'] === 'Recibido' && $direccionAnterior !== 'Recibido';
            $pasaAExpedido   = $data['direction'] === 'Expedido' && $direccionAnterior === 'Recibido';
            $requiereReabrir = $cambioOficina || $pasaARecibido;

            if ($requiereReabrir) {
                // Cambió de oficina (o ahora sí requiere atención): vuelve a quedar pendiente
                $payload['estado_atencion'] = 'pendiente';
                $payload['tomado_por_id']   = null;
                $payload['tomado_en']       = null;
            } elseif ($pasaAExpedido) {
                // Ya no le corresponde atención a ninguna oficina
                $payload['estado_atencion'] = null;
                $payload['tomado_por_id']   = null;
                $payload['tomado_en']       = null;
            }

            $novedad->update($payload);

            if ($requiereReabrir || $pasaAExpedido) {
                // Invalida las notificaciones viejas: ya no le corresponden a la oficina/estado anterior
                DatabaseNotification::where('data->novedad_id', $novedad->id)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }

            if ($requiereReabrir && $novedad->office_id) {
                $destinatarios = User::where('oficina_id', $novedad->office_id)
                    ->where('id', '!=', Auth::id())
                    ->get();

                if ($destinatarios->isNotEmpty()) {
                    Notification::send($destinatarios, new NovedadUrgenteNotification($novedad));
                }
            }

            if ($requiereReabrir || $pasaAExpedido) {
                // Refresco inmediato del badge de estado para quien está editando (no espera el poll)
                $this->dispatch('novedad-estado-actualizado', novedadId: $novedad->id);
            }
        } else {
            $this->authorize('create', [News::class, $this->guardia]);

            $novedad = $this->guardia->novedades()->create([
                ...$payload,
                'user_id'         => Auth::id(),
                'estado_atencion' => $data['direction'] === 'Recibido' ? 'pendiente' : null,
            ]);

            if (!empty($this->archivos)) {
                $fecha      = $this->guardia->date->format('dmY');
                $carpeta    = $data['direction'] === 'Recibido' ? 'Recibidos' : 'Expedidos';
                $directorio = "{$fecha}/{$carpeta}";

                foreach ($this->archivos as $archivo) {
                    $nombre = time() . '_' . uniqid() . '_' . $archivo->getClientOriginalName();
                    $ruta   = $archivo->storeAs($directorio, $nombre, 'guardias');

                    Attach::create([
                        'news_id'   => $novedad->id,
                        'user_id'   => Auth::id(),
                        'file_name' => $archivo->getClientOriginalName(),
                        'file_path' => $ruta,
                        'file_type' => $archivo->getMimeType(),
                        'file_size' => $archivo->getSize(),
                    ]);
                }
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

        unset($this->novedadesAgrupadas);
        $this->dispatch('guardia-contador-actualizado', tipo: 'novedades', guardiaId: $this->guardia->id);
        $this->dispatch('cerrar-modal-novedad');
    }

    public function eliminar(int $id): void
    {
        $novedad = $this->guardia->novedades()->findOrFail($id);
        $this->authorize('delete', $novedad);

        $novedad->delete();
        unset($this->novedadesAgrupadas);

        $this->dispatch('guardia-contador-actualizado', tipo: 'novedades', guardiaId: $this->guardia->id);
    }

    public function render()
    {
        return view('components.novedades-guardia.novedades-guardia');
    }
};