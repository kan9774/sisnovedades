<?php

namespace App\Livewire;

use App\Models\Guard;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CorreosFallidosList extends Component
{
    public Guard $guardia;

    public function render()
    {
        $correosFallidos = DB::table('guardia_correos_fallidos')
            ->where('guardia_id', $this->guardia->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.correos-fallidos-list', compact('correosFallidos'));
    }
}