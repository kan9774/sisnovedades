<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\News;
use App\Models\Guard;

class RealTimeNews extends Component
{
    public $guardia;
    public $novedades;
    public $recibidos = [];
    public $expedidos = [];
    
    protected $listeners = [
        'novedad-creada' => 'refreshNews',
        'novedad-editada' => 'refreshNews',
        'novedad-eliminada' => 'refreshNews'
    ];

    public function mount(Guard $guardia = null)
    {
        $this->guardia = $guardia;
        if ($guardia) {
            $this->loadNews();
        }
    }

    public function loadNews()
    {
        if ($this->guardia) {
            $novedades = $this->guardia->novedades()
                ->with(['escribiente', 'tomadoPor', 'organismo', 'oficina'])
                ->latest()
                ->take(20)
                ->get();

            $this->recibidos = $novedades->where('direction', 'Recibido');
            $this->expedidos = $novedades->where('direction', 'Expedido');
        }
    }

    public function refreshNews()
    {
        $this->loadNews();
    }

    public function render()
    {
        return view('livewire.real-time-news');
    }
}