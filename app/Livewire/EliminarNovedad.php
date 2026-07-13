<?php

namespace App\Livewire;

use App\Models\Guard;
use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class EliminarNovedad extends Component
{
    public News $novedad;
    public Guard $guardia;

    public function mount(News $novedad, Guard $guardia): void
    {
        $this->novedad = $novedad;
        $this->guardia = $guardia;
    }

    public function eliminar()
    {
        $this->authorize('delete', $this->novedad);

        foreach ($this->novedad->adjuntos as $adjunto) {
            Storage::disk('guardias')->delete($adjunto->file_path);
            $adjunto->delete();
        }

        $this->novedad->delete();

        return $this->redirect(
            route('admin.guardias.show', $this->guardia) . '#tab-novedades',
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.eliminar-novedad');
    }
}