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
    public string $fecha = '';
    public string $tipo = '';
    public string $texto = '';

    // Estado de edición inline
    public ?int $editingId = null;
    public string $editHora = '';
    public string $editFecha = '';
    public string $editTipo = '';
    public string $editTexto = '';

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
            'fecha' => 'nullable|date_format:Y-m-d',
            'tipo'  => 'required|string|max:100',
            'texto' => 'required|string|max:1000',
        ]);

        $fecha = $data['fecha'] ?: $this->inferirFecha();

        $this->guardia->novedadesPersonal()->create([
            ...$data,
            'fecha' => $fecha,
            'user_id' => auth()->id(),
        ]);

        $this->reset(['hora', 'tipo', 'texto']);
        unset($this->novedades); // limpia la caché del computed para que se vea el nuevo registro
        $this->dispatch('guardia-contador-actualizado', tipo: 'personal', guardiaId: $this->guardia->id);
    }

    public function eliminar(int $id): void
    {
        abort_unless($this->puedeOperarGuardia && $this->guardia->status === 'open', 403);

        // si se elimina el registro que se estaba editando, cancelamos la edición
        if ($this->editingId === $id) {
            $this->cancelarEdicion();
        }

        $this->guardia->novedadesPersonal()->whereKey($id)->delete();
        unset($this->novedades);
        $this->dispatch('guardia-contador-actualizado', tipo: 'personal', guardiaId: $this->guardia->id);
    }

    public function editar(int $id): void
    {
        abort_unless($this->puedeOperarGuardia && $this->guardia->status === 'open', 403);

        $item = $this->guardia->novedadesPersonal()->whereKey($id)->firstOrFail();

        $this->editingId  = $item->id;
        $this->editHora   = $item->hora->format('H:i');
        $this->editFecha  = $item->fecha?->format('Y-m-d') ?? '';
        $this->editTipo   = $item->tipo;
        $this->editTexto  = $item->texto;

        // limpia errores de validación de otra fila que se haya quedado abierta
        $this->resetErrorBag(['editHora', 'editFecha', 'editTipo', 'editTexto']);
    }

    public function cancelarEdicion(): void
    {
        $this->reset(['editingId', 'editHora', 'editFecha', 'editTipo', 'editTexto']);
        $this->resetErrorBag(['editHora', 'editFecha', 'editTipo', 'editTexto']);
    }

    public function guardarEdicion(): void
    {
        abort_unless($this->puedeOperarGuardia && $this->guardia->status === 'open', 403);

        if (!$this->editingId) {
            return;
        }

        $data = $this->validate([
            'editHora'  => 'required|date_format:H:i',
            'editFecha' => 'nullable|date_format:Y-m-d',
            'editTipo'  => 'required|string|max:100',
            'editTexto' => 'required|string|max:1000',
        ]);

        $fecha = $data['editFecha'] ?: $this->inferirFecha();

        $this->guardia->novedadesPersonal()->whereKey($this->editingId)->update([
            'hora'  => $data['editHora'],
            'fecha' => $fecha,
            'tipo'  => $data['editTipo'],
            'texto' => $data['editTexto'],
        ]);

        $this->reset(['editingId', 'editHora', 'editFecha', 'editTipo', 'editTexto']);
        unset($this->novedades);
    }

    #[Computed]
    public function novedades()
    {
        return $this->guardia->novedadesPersonal()->orderBy('fecha')->orderBy('hora')->paginate(8);
    }

    private function inferirFecha(): string
    {
        $ultimo = $this->guardia->novedadesPersonal()
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->first();

        return $ultimo?->fecha?->format('Y-m-d') ?? $this->guardia->date->format('Y-m-d');
    }

    public function render()
    {
        return view('components.novedades-personal.novedades-personal');
    }
};