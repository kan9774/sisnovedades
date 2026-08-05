<?php

namespace App\Livewire\Landing;

use Livewire\Component;

class Recreacion extends Component
{
    // null = menú de selección. Con valor = juego activo.
    public ?string $juego = null;

    public function elegir(string $juego): void
    {
        $this->juego = $juego;
    }

    public function volverAlMenu(): void
    {
        $this->juego = null;
    }

    public function render()
    {
        return view('livewire.landing.recreacion');
    }
}