<?php

namespace App\Livewire\Landing;

use Livewire\Component;

class MainOrquestador extends Component
{
    // Este estado define qué componente Livewire renderizar a la derecha
    public $seccionActual = 'inicio'; 

    // Escuchamos el evento que disparará el menú desde el Navbar izquierdo
    protected $listeners = ['cambiarSeccion'];

    public function cambiarSeccion($nuevaSeccion)
    {
        $this->seccionActual = $nuevaSeccion;
    }

    public function render()
    {
        return view('livewire.landing.main-orquestador');
    }
}