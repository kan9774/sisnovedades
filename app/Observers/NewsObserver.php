<?php

namespace App\Observers;

use App\Models\News;
use Illuminate\Support\Facades\Storage;

class NewsObserver
{
    public function deleting(News $news): void
    {
        // Cargar adjuntos antes de eliminar
        foreach ($news->adjuntos as $adjunto) {
            // Borrar archivo físico del servidor
            Storage::disk('guardias')->delete($adjunto->file_path);
            // Borrar registro de la BD
            $adjunto->delete();
        }
    }
}