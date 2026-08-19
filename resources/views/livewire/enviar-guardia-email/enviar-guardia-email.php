<?php

use App\Jobs\EnviarNovedadGuardiaMail;
use App\Jobs\EnviarNovedadesGuardiaLoteJob;
use App\Models\Guard;
use App\Models\GuardiaPdfDestinatario;
use App\Models\User;
use App\Support\GuardiaPdfGenerator;
use App\Support\GuardiaZipGenerator;
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
    public bool $enviarZip = false;
    public string $mensajeExito = '';

    public function mount(Guard $guardia, bool $puedeOperarGuardia = false): void
    {
        $this->guardia = $guardia;
        $this->puedeOperarGuardia = $puedeOperarGuardia;
    }

    /**
     * "Incluir adjuntos" (embebidos en el PDF) y "Enviar ZIP" son
     * mutuamente excluyentes: la primera opción ya es pesada de procesar
     * (fusión FPDI + imágenes embebidas), y sumarle el armado del ZIP
     * en el mismo envío puede tirar timeout o memory limit. Al tildar
     * una, se destilda la otra automáticamente.
     */
    public function updatedIncluirAdjuntos(bool $valor): void
    {
        if ($valor) {
            $this->enviarZip = false;
        }
    }

    public function updatedEnviarZip(bool $valor): void
    {
        if ($valor) {
            $this->incluirAdjuntos = false;
        }
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
        $this->enviarZip = false;
        $this->mensajeExito = '';
        $this->dispatch('abrir-modal-enviar-guardia');
    }

    public function enviar(): void
    {
        abort_unless($this->puedeOperarGuardia, 403);

        // Garantía dura, no solo cosmética: nunca se procesan las dos
        // opciones juntas, sin importar cómo haya llegado el estado hasta
        // acá. Si por lo que sea las dos vinieran en true, el ZIP gana y
        // la otra se apaga ANTES de generar nada — así nunca se paga el
        // costo de armar el PDF con adjuntos embebidos si al final no se
        // va a usar.
        if ($this->enviarZip) {
            $this->incluirAdjuntos = false;
        }

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

        // El PDF es el mismo para todos los destinatarios de esta guardia
        // (no depende del usuario) — se genera UNA sola vez acá afuera y
        // se reutiliza en los N envíos, en vez de que cada uno dispare su
        // propio render de DomPDF + fusión de FPDI.
        $pdfContent = $this->incluirAdjuntos
            ? GuardiaPdfGenerator::generarConAdjuntos($this->guardia)
            : GuardiaPdfGenerator::generar($this->guardia)->output();

        // El ZIP (PDF + adjuntos crudos, sin embeber) también se arma una
        // sola vez y se reutiliza en los N envíos. Mutuamente excluyente
        // con $incluirAdjuntos (ver updatedIncluirAdjuntos/updatedEnviarZip).
        $zipContent = $this->enviarZip
            ? GuardiaZipGenerator::generar($this->guardia, $pdfContent)
            : null;

        // La respuesta HTTP se envía al navegador ANTES de procesar los N
        // envíos de correo, evitando el timeout HTTP 503 del front-end (IIS).
        // Los envíos ocurren en segundo plano dentro del mismo request pero
        // después de que la respuesta ya fue entregada.
        $this->dispatch('novedades-enviadas');

        $this->destinatarios = [];
        $this->grupoSeleccionado = null;
        $this->mensajeExito = 'Enviando novedades por correo...';

                // Log de actividad sincrónico: se registra ANTES de despachar el
        // job, ya que solo describe la intención de envío (no depende de
        // que los correos individuales salgan bien o mal).
        activity('Guardias')
            ->performedOn($this->guardia)
            ->causedBy(Auth::user())
            ->withProperties([
                'destinatarios' => $usuarios->pluck('email'),
                'modo' => $this->modoSeleccion,
                'con_adjuntos' => $this->incluirAdjuntos,
                'con_zip' => $this->enviarZip,
            ])
            ->log("Envió las novedades de la guardia por correo a {$usuarios->count()} destinatario(s).");

        // Envío asíncrono (después de responder): se usa un Job real (no
        // un closure) porque un closure definido acá capturaría
        // implícitamente $this (este componente Livewire, una clase
        // anónima no serializable) — eso rompía la serialización del job
        // incluso con driver 'sync', tirando la excepción DESPUÉS de que
        // la respuesta HTTP ya se había enviado (sin error visible, sin
        // correos salientes). Ver EnviarNovedadesGuardiaLoteJob.
        dispatch(new EnviarNovedadesGuardiaLoteJob(
            $this->guardia,
            $usuarios->pluck('id')->all(),
            $nombreRemitente,
            $this->incluirAdjuntos,
            $pdfContent,
            $this->enviarZip,
            $zipContent,
        ))->afterResponse();
    }

    public function render()
    {
        return view('livewire.enviar-guardia-email.enviar-guardia-email');
    }
};