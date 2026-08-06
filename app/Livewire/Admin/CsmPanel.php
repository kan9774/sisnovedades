<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\ContratoCsmService;
use Carbon\Carbon;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsmPanel extends Component
{
    public User $user;

    public bool $mostrarModal = false;
    public ?int $aniosSeleccionados = null;
    public string $fechaFirma = '';

    public function mount(User $user)
    {
        $this->user = $user;
        $this->fechaFirma = now()->toDateString();
    }

    /**
     * Plazos disponibles: años configurados en csm.plantillas cuyo archivo
     * realmente existe en storage (para no mostrar botones de anexos que
     * todavía no subiste).
     */
    public function getPerteneceAUnidadProperty(): bool
    {
        return $this->user->unidad_id === config('csm.unidad_id');
    }

    public function getPlazosDisponiblesProperty(): array
    {
        if (!$this->perteneceAUnidad) {
            return [];
        }

        $disponibles = [];

        foreach (config('csm.plantillas') as $anios => $archivo) {
            $ruta = config('csm.plantillas_path') . DIRECTORY_SEPARATOR . $archivo;
            if (file_exists($ruta)) {
                $disponibles[$anios] = $anios == 1 ? '1 año' : "$anios años";
            }
        }

        return $disponibles;
    }

    public function abrirModal(int $anios)
    {
        $this->aniosSeleccionados = $anios;
        $this->fechaFirma = now()->toDateString();
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->aniosSeleccionados = null;
    }

    public function generar(): ?StreamedResponse
    {
        $this->validate([
            'fechaFirma' => ['required', 'date'],
            'aniosSeleccionados' => ['required', 'integer', 'min:1'],
        ]);

        $service = app(ContratoCsmService::class);

        try {
            $path = $service->generar(
                $this->user,
                $this->aniosSeleccionados,
                Carbon::parse($this->fechaFirma)
            );
        } catch (\Throwable $e) {
            $this->addError('fechaFirma', 'No se pudo generar el contrato: ' . $e->getMessage());
            return null;
        }

        $nombreDescarga = basename($path);
        $this->cerrarModal();

        return response()->streamDownload(function () use ($path) {
            echo file_get_contents($path);
            @unlink($path); // limpiar el temporal después de servirlo
        }, $nombreDescarga, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.csm-panel');
    }
}