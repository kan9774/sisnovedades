<?php

namespace App\Http\Controllers;

use App\Models\Attach;
use App\Models\Guard;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdjuntoController extends Controller
{

    public function download(Guard $guardia, News $novedad, Attach $adjunto)
    {
        return Storage::disk('guardias')->download($adjunto->file_path, $adjunto->file_name);
    }

    public function view(Guard $guardia, News $novedad, Attach $adjunto)
    {
        abort_if($novedad->guard_id !== $guardia->id, 404);
        abort_if($adjunto->news_id !== $novedad->id, 404);

        $url = Storage::disk('guardias')->url($adjunto->file_path);

        return redirect($url);
    }
}
