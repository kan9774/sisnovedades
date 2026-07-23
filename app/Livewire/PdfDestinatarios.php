<?php

namespace App\Livewire;

use App\Models\Guard;
use App\Models\GuardiaPdfDestinatario;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class PdfDestinatarios extends Component
{
    use WithPagination;

    // Estado UI
    public $creating = false;
    public $editing = false;
    public $assigning = false;
    public $showTrashed = false;

    // Formulario
    public $nombre = '';
    public $descripcion = '';
    public $detalles = '';
    public $color = '#3498db';
    public $destinatarioId = null;

    // Asignación de usuarios
    public $selectedUsers = [];

    public function __invoke()
    {
        return $this->render();
    }

    public function mount(): void
    {
        $this->checkAuthorization();
        $this->selectedUsers = [];
    }

    /**
     * Verifica que el usuario tenga permiso para acceder.
     * - Superadmin (is_super_admin = 1)
     * - Miembro de la guardia del día (capitán, oficial, escribiente)
     */
    private function checkAuthorization(): void
    {
        $user = auth()->user();
        
        // Si es superadmin
        if ($user->is_super_admin) {
            return;
        }

        // Si es miembro de la guardia del día
        $guardiaHoy = Guard::whereDate('date', today())->first();
        if ($guardiaHoy && $guardiaHoy->esMiembro($user)) {
            return;
        }

        // No tiene acceso
        abort(403, 'Para acceder a destinatarios PDF debes ser miembro de la guardia del día o ser SuperAdmin.');
    }

    public function render()
    {
        $users = User::with('oficina')->orderBy('name')->get();
        
        return view('livewire.pdf-destinatarios', [
            'destinatarios' => $this->getDestinatarios(),
            'users' => $users,
            'creating' => $this->creating,
            'editing' => $this->editing,
            'assigning' => $this->assigning,
            'showTrashed' => $this->showTrashed,
            'destinatarioId' => $this->destinatarioId,
        ]);
    }

    private function getDestinatarios()
    {
        $query = GuardiaPdfDestinatario::query();
        if (!$this->showTrashed) {
            $query->whereNull('deleted_at');
        }
        return $query->orderBy('nombre')->paginate(10);
    }

    public function toggleTrashed(): void
    {
        $this->showTrashed = !$this->showTrashed;
        $this->resetPage();
    }

    // --- CRUD ---
    public function crear(): void
    {
        $this->reset(['nombre', 'descripcion', 'detalles', 'color', 'destinatarioId']);
        $this->creating = true;
        $this->editing = false;
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre'     => 'required|string|max:255',
            'descripcion'=> 'nullable|string|max:255',
            'detalles'   => 'nullable|string',
            'color'      => 'required|string|max:7',
        ]);

        if ($this->editing) {
            $dest = GuardiaPdfDestinatario::findOrFail($this->destinatarioId);
            $dest->update($this->only(['nombre', 'descripcion', 'detalles', 'color']));
        } else {
            GuardiaPdfDestinatario::create($this->only(['nombre', 'descripcion', 'detalles', 'color']));
        }

        $this->dispatch('destinatario-guardado');
        $this->resetForm();
    }

    public function editar($id): void
    {
        $dest = GuardiaPdfDestinatario::withTrashed()->findOrFail($id);
        $this->destinatarioId = $dest->id;
        $this->nombre = $dest->nombre;
        $this->descripcion = $dest->descripcion;
        $this->detalles = $dest->detalles;
        $this->color = $dest->color;
        $this->editing = true;
        $this->creating = false;
    }

    public function eliminar($id): void
    {
        GuardiaPdfDestinatario::findOrFail($id)->delete();
        $this->dispatch('destinatario-eliminado');
    }

    public function restaurar($id): void
    {
        GuardiaPdfDestinatario::withTrashed()->findOrFail($id)->restore();
        $this->dispatch('destinatario-restaurado');
    }

    // --- Asignar Usuarios ---
    public function asignar($id): void
    {
        $this->destinatarioId = $id;
        $dest = GuardiaPdfDestinatario::findOrFail($id);
        $this->selectedUsers = $dest->usuarios->pluck('id')->toArray();
        $this->assigning = true;
    }

    public function cerrarAsignacion(): void
    {
        $this->assigning = false;
        $this->destinatarioId = null;
        $this->selectedUsers = [];
    }

    public function guardarAsignacion(): void
    {
        $dest = GuardiaPdfDestinatario::findOrFail($this->destinatarioId);
        $dest->usuarios()->sync($this->selectedUsers);
        $this->dispatch('destinatario-actualizado');
        $this->assigning = false;
    }

    public function toggleAllUsers(): void
    {
        $allIds = User::pluck('id')->toArray();
        $this->selectedUsers = count($this->selectedUsers) === count($allIds) ? [] : $allIds;
    }

    public function resetForm(): void
    {
        $this->creating = false;
        $this->editing = false;
        $this->reset(['nombre', 'descripcion', 'detalles', 'color', 'destinatarioId']);
        $this->resetPage();
    }
}