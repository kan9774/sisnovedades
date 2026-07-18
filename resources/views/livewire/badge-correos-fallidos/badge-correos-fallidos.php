<?php

use App\Models\Guard;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Guard $guardia;

    public function mount(Guard $guardia): void
    {
        $this->guardia = $guardia;
    }

    #[Computed]
    public function pendientes(): int
    {
        return DB::table('guardia_correos_fallidos')
            ->where('guardia_id', $this->guardia->id)
            ->whereNull('resuelto_at')
            ->count();
    }

    #[On('correos-fallidos-actualizado')]
    public function refrescar(): void
    {
        unset($this->pendientes);
    }

    public function render()
    {
        return view('livewire.badge-correos-fallidos.badge-correos-fallidos');
    }
};