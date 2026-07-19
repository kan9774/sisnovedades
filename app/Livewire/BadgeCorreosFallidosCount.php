<?php

namespace App\Livewire;

use App\Models\Guard;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class BadgeCorreosFallidosCount extends Component
{
    public Guard $guardia;

    #[On('novedades-enviadas')]
    #[On('correos-fallidos-actualizado')]
    public function refrecar(): void
    {
     
    }
    
    public function render(): View
    {
        $cantidad = DB::table('guardia_correos_fallidos')
            ->where('guardia_id', $this->guardia->id)
            ->count();

        return view('livewire.badge-correos-fallidos-count', compact('cantidad'));
    }
}