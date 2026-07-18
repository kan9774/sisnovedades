<?php

use App\Jobs\EnviarNovedadGuardiaMail;
use App\Models\Guard;
use App\Models\GuardiaPdfDestinatario;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Guard $guardia;
    public bool $puedeOperarGuardia = false;

    public string $modoSeleccion = 'manual'; // 'manual' | 'grupo'
    public array $destinatarios = [];
    public ?int $grupoSeleccionado = null;
    public bool $incluirAdjuntos = false;
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

    #[Computed]
    public function grupos()
    {
        return GuardiaPdfDestinatario::whereNull('deleted_at')
            ->withCount('usuarios')
            ->orderBy('nombre')
            ->get();
    }

    public function abrir(): void
    {
        $this->resetValidation();
        $this->destinatarios = [];
        $this->grupoSeleccionado = null;
        $this->modoSeleccion = 'manual';
        $this->incluirAdjuntos = false;
        $this->mensajeExito = '';
        $this->dispatch('abrir-modal-enviar-guardia');
    }

    public function enviar(): void
    {
        abort_unless($this->puedeOperarGuardia, 403);

        $this->mensajeExito = '';

        if ($this->modoSeleccion === 'grupo') {
            $this->validate([
                'grupoSeleccionado' => 'required|exists:guardia_pdf_destinatarios,id',
            ], [
                'grupoSeleccionado.required' => 'Elegí un grupo de destinatarios.',
            ]);

            $grupo = GuardiaPdfDestinatario::findOrFail($this->grupoSeleccionado);
            $usuarios = $grupo->usuarios()->whereNotNull('email')->get();

            if ($usuarios->isEmpty()) {
                $this->addError('grupoSeleccionado', 'Ese grupo no tiene usuarios con email cargado.');
                return;
            }
        } else {
            $this->validate([
                'destinatarios'   => 'required|array|min:1',
                'destinatarios.*' => 'exists:users,id',
            ], [
                'destinatarios.required' => 'Elegí al menos un destinatario.',
            ]);

            $usuarios = User::whereIn('id', $this->destinatarios)
                ->whereNotNull('email')
                ->get();
        }

        $nombreRemitente = Auth::user()->name . ' ' . Auth::user()->last_name;

        // Envío sincrónico (sin cola): para este volumen de destinatarios es
        // más simple y confiable que depender de un worker (queue:work)
        // corriendo en segundo plano. Subimos el límite de tiempo por si
        // el envío de todos los correos tarda más que el máximo por defecto.
        set_time_limit(120);

        $fallidos = 0;

        foreach ($usuarios as $usuario) {
            $enviado = EnviarNovedadGuardiaMail::dispatchSync(
                $this->guardia,
                $usuario,
                $nombreRemitente,
                $this->incluirAdjuntos,
            );

            if ($enviado === false) {
                $fallidos++;
            }
        }

        activity('Guardias')
            ->performedOn($this->guardia)
            ->causedBy(Auth::user())
            ->withProperties([
                'destinatarios' => $usuarios->pluck('email'),
                'modo' => $this->modoSeleccion,
                'con_adjuntos' => $this->incluirAdjuntos,
            ])
            ->log("Envió las novedades de la guardia por correo a {$usuarios->count()} destinatario(s).");

        $this->destinatarios = [];
        $this->grupoSeleccionado = null;
        $this->mensajeExito = $fallidos > 0
            ? "Se enviaron {$usuarios->count()} correo(s), {$fallidos} fallaron (ver guardia_correos_fallidos)."
            : 'Se enviaron ' . $usuarios->count() . ' correo(s) correctamente.';

        $this->dispatch('novedades-enviadas');
    }

    public function render()
    {
        return view('livewire.enviar-guardia-email.enviar-guardia-email');
    }
};