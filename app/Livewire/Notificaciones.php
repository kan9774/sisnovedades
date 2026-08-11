<?php

namespace App\Livewire;

use App\Models\News;
use App\Services\NovedadService;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class Notificaciones extends Component
{
    use WithPagination;

    public $filtro = 'todas';
    public $successMsg = '';
    public $errorMsg = '';

    #[Computed]
    public function notificaciones()
    {
        $query = auth()->user()->notifications();

        if ($this->filtro === 'no_leidas') {
            $query = auth()->user()->unreadNotifications();
        }

        return $query->paginate(15);
    }

    public function cambiarFiltro($nuevoFiltro)
    {
        $this->filtro = $nuevoFiltro;
        $this->resetPage();
    }

    public function marcarLeida(string $id)
    {
        $notificacion = auth()->user()->notifications()->findOrFail($id);
        [$novedadId, $guardiaId] = NovedadService::marcarLeida($notificacion);

        if ($novedadId && $guardiaId) {
            return $this->redirect(route('admin.guardias.novedades.show', [$guardiaId, $novedadId]));
        }

        $this->successMsg = 'Notificación marcada como leída.';
    }

    public function marcarTodasLeidas()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->successMsg = 'Todas las notificaciones fueron marcadas como leídas.';
    }

    public function render()
    {
        return view('livewire.notificaciones.index', [
            'notificaciones' => $this->notificaciones(),
        ]);
    }
}
