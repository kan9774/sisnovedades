<?php

use App\Mail\GuardiaNovedadesMail;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

        foreach ($usuarios as $usuario) {
            Mail::to($usuario->email)->send(
                new GuardiaNovedadesMail($this->guardia, Auth::user()->name . ' ' . Auth::user()->last_name)
            );
        }

        activity('Guardias')
            ->performedOn($this->guardia)
            ->causedBy(Auth::user())
            ->withProperties(['destinatarios' => $usuarios->pluck('email')])
            ->log("Envió las novedades de la guardia por correo a {$usuarios->count()} destinatario(s).");

        $this->destinatarios = [];
        $this->mensajeExito = 'Correo enviado a ' . $usuarios->count() . ' destinatario(s) correctamente.';
    }
};