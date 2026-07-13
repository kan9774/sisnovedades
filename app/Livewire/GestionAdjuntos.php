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

    public $archivo = null;

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

    public function updatedArchivo(): void
    {
        // Sube apenas se selecciona el archivo, sin botón "Subir" aparte
        $this->subir();
    }

    public function subir(): void
    {
        $this->authorize('upload-attach', $this->novedad);

        if (!$this->archivo) {
            return;
        }

        $this->validate([
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        // Reemplazo: si ya hay un adjunto, se borra antes de crear el nuevo
        $anterior = $this->novedad->adjuntos()->first();
        if ($anterior) {
            Storage::disk('guardias')->delete($anterior->file_path);
            $anterior->delete();
        }

        $fecha      = $this->guardia->date->format('dmY');
        $carpeta    = $this->novedad->direction === 'Recibido' ? 'Recibidos' : 'Expedidos';
        $directorio = "{$fecha}/{$carpeta}";

        $nombre = time() . '_' . basename($this->archivo->getClientOriginalName());
        $ruta   = $this->archivo->storeAs($directorio, $nombre, 'guardias');

        Attach::create([
            'news_id'   => $this->novedad->id,
            'user_id'   => Auth::id(),
            'file_name' => $this->archivo->getClientOriginalName(),
            'file_path' => $ruta,
            'file_type' => $this->archivo->getMimeType(),
            'file_size' => $this->archivo->getSize(),
        ]);

        $this->archivo = null;
        unset($this->adjuntos);

        session()->flash('adjunto-success', 'Archivo adjuntado correctamente.');
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