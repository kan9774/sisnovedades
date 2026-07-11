<?php

use App\Jobs\EnviarNovedadGuardiaMail;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Guard $guardia;
    public bool $puedeOperarGuardia = false;

    public array $destinatarios = [];
    public string $mensajeExito = '';

    public function mount(Guard $guardia, bool $puedeOperarGuardia = false): void
    {
        $this->guardia = $guardia;
        $this->puedeOperarGuardia = $puedeOperarGuardia;
    }

    #[Computed]
    public function usuariosPorOficina()
    {
        return User::whereNotNull('email')
            ->where('status', 'active')
            ->with('oficina')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($usuario) => $usuario->oficina->nombre ?? 'Sin oficina asignada');
    }

    public function abrir(): void
    {
        $this->resetValidation();
        $this->destinatarios = [];
        $this->mensajeExito = '';
        $this->dispatch('abrir-modal-enviar-guardia');
    }

    public function enviar(): void
    {
        abort_unless($this->puedeOperarGuardia, 403);

        $this->mensajeExito = '';

        $this->validate([
            'destinatarios'   => 'required|array|min:1',
            'destinatarios.*' => 'exists:users,id',
        ], [
            'destinatarios.required' => 'Elegí al menos un destinatario.',
        ]);

        $usuarios = User::whereIn('id', $this->destinatarios)
            ->whereNotNull('email')
            ->get();

        $nombreRemitente = Auth::user()->name . ' ' . Auth::user()->last_name;

        // Escalonamos el envío 2 segundos entre cada correo para no saturar el
        // servidor SMTP con ráfagas grandes (relevante cuando crezca a 100-200+ destinatarios).
        foreach ($usuarios as $index => $usuario) {
            EnviarNovedadGuardiaMail::dispatch($this->guardia, $usuario, $nombreRemitente)
                ->delay(now()->addSeconds($index * 2));
        }

        activity('Guardias')
            ->performedOn($this->guardia)
            ->causedBy(Auth::user())
            ->withProperties(['destinatarios' => $usuarios->pluck('email')])
            ->log("Encoló el envío de las novedades de la guardia por correo a {$usuarios->count()} destinatario(s).");

        $this->destinatarios = [];
        $this->mensajeExito = 'Se encolaron ' . $usuarios->count() . ' correo(s) para su envío.';

        $this->dispatch('novedades-enviadas');
    }
};