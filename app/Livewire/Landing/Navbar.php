<?php

namespace App\Livewire\Landing;

use Livewire\Attributes\On;
use Livewire\Component;

class Navbar extends Component
{
    #[On('nueva-novedad')]
    public function refrescarNotificaciones(): void
    {
        // No necesita hacer nada: el solo hecho de que Livewire ejecute
        // este método fuerza un re-render, y $totalNoLeidas se recalcula
        // en el @php del navbar.blade.php con el conteo actualizado.
    }

    public function render()
    {
        return view('livewire.landing.navbar');
    }
}