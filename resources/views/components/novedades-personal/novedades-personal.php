<?php

use App\Models\Guard;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public Guard $guardia;
    public bool $puedeOperarGuardia = false;

    public string $hora = '';
    public string $tipo = '';
    public string $texto = '';

    public function mount(Guard $guardia, bool $puedeOperarGuardia = false): void
    {
        $this->guardia = $guardia;
        $this->puedeOperarGuardia = $puedeOperarGuardia;
    }

    public function agregar(): void
    {
        abort_unless($this->puedeOperarGuardia && $this->guardia->status === 'open', 403);

        $data = $this->validate([
            'hora'  => 'required|date_format:H:i',
            'tipo'  => 'required|string|max:100',
            'texto' => 'required|string|max:1000',
        ]);

        $this->guardia->novedadesPersonal()->create([...$data, 'user_id' => auth()->id()]);

        $this->reset(['hora', 'tipo', 'texto']);
        unset($this->novedades); // limpia la caché del computed para que se vea el nuevo registro
         $this->dispatch('guardia-contador-actualizado', tipo: 'personal', guardiaId: $this->guardia->id);
    }

    public function eliminar(int $id): void
    {
        abort_unless($this->puedeOperarGuardia && $this->guardia->status === 'open', 403);

        $this->guardia->novedadesPersonal()->whereKey($id)->delete();
        unset($this->novedades);
        $this->dispatch('guardia-contador-actualizado', tipo: 'personal', guardiaId: $this->guardia->id);
    }

    #[Computed]
    public function novedades()
    {
        return $this->guardia->novedadesPersonal()->orderBy('hora')->paginate(8);
    }

    public function render()
    {
        return view('components.novedades-personal.novedades-personal');
    }
};