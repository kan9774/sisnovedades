<?php

namespace App\Livewire;

use App\Models\Attach;
use App\Models\Guard;
use App\Models\News;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class GestionAdjuntos extends Component
{
    use WithFileUploads;

    public News $novedad;
    public Guard $guardia;

    public array $archivos = [];

    public function mount(News $novedad, Guard $guardia): void
    {
        $this->novedad = $novedad;
        $this->guardia = $guardia;
    }

    #[Computed]
    public function puedeGestionar(): bool
    {
        return $this->guardia->status === 'open'
            && ($this->guardia->esMiembro(auth()->user()) || auth()->user()->isAdmin());
    }

    #[Computed]
    public function adjuntos()
    {
        return $this->novedad->adjuntos()->latest()->get();
    }

    public function updatedArchivos(): void
    {
        // Sube apenas se seleccionan los archivos, sin botón "Subir" aparte
        $this->subir();
    }

    public function subir(): void
    {
        $this->authorize('upload-attach', $this->novedad);

        if (empty($this->archivos)) {
            return;
        }

        $this->validate([
            'archivos' => ['array', 'max:5'],
            'archivos.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $totalActual = $this->novedad->adjuntos()->count();
        if ($totalActual + count($this->archivos) > 8) {
            $this->addError('archivos', "Esta novedad ya tiene {$totalActual} adjunto(s); el máximo total es 8.");
            return;
        }

        // Ya NO se borra el adjunto anterior: cada archivo seleccionado se
        // suma a los que ya tiene la novedad, hasta el límite de arriba.

        $fecha      = $this->guardia->date->format('dmY');
        $carpeta    = $this->novedad->direction === 'Recibido' ? 'Recibidos' : 'Expedidos';
        $directorio = "{$fecha}/{$carpeta}";

        foreach ($this->archivos as $archivo) {
            $nombre = time() . '_' . uniqid() . '_' . basename($archivo->getClientOriginalName());
            $ruta   = $archivo->storeAs($directorio, $nombre, 'guardias');

            Attach::create([
                'news_id'   => $this->novedad->id,
                'user_id'   => Auth::id(),
                'file_name' => $archivo->getClientOriginalName(),
                'file_path' => $ruta,
                'file_type' => $archivo->getMimeType(),
                'file_size' => $archivo->getSize(),
            ]);
        }

        $this->archivos = [];
        unset($this->adjuntos);

        session()->flash('adjunto-success', 'Archivo(s) adjuntado(s) correctamente.');
        $this->dispatch('adjunto-actualizado');
    }

    public function eliminar(int $adjuntoId): void
    {
        $this->authorize('upload-attach', $this->novedad);

        $adjunto = $this->novedad->adjuntos()->findOrFail($adjuntoId);

        Storage::disk('guardias')->delete($adjunto->file_path);
        $adjunto->delete();

        unset($this->adjuntos);
        $this->dispatch('adjunto-actualizado');
    }

    public function render()
    {
        return view('livewire.gestion-adjuntos');
    }
}