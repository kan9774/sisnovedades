<?php

namespace App\Livewire;

use App\Models\Guard;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BadgeCorreosFallidosCount extends Component
{
    public Guard $guardia;

    public function render()
    {
        $cantidad = DB::table('guardia_correos_fallidos')
            ->where('guardia_id', $this->guardia->id)
            ->count();

        return view('livewire.badge-correos-fallidos-count', compact('cantidad'));
    }
}