<?php

namespace App\Jobs;

use App\Mail\GuardiaNovedadesMail;
use App\Models\Guard;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envío directo (sin cola) de las novedades de guardia por correo.
 *
 * Antes implementaba ShouldQueue, pero eso requería un worker
 * (queue:work) corriendo permanentemente, algo frágil de sostener
 * en este entorno. Para el volumen actual (~30 destinatarios) se
 * envía de forma sincrónica llamando a dispatchSync(), que ejecuta
 * handle() directamente y devuelve su resultado, sin pasar por la
 * tabla "jobs".
 */
class EnviarNovedadGuardiaMail
{
    use Dispatchable, Queueable;

    public function __construct(
        public Guard $guardia,
        public User $usuario,
        public string $nombreRemitente,
        public bool $incluirAdjuntos = false,
    ) {}

    public function handle(): bool
    {
        try {
            Mail::to($this->usuario->email)->send(
                new GuardiaNovedadesMail($this->guardia, $this->nombreRemitente, $this->incluirAdjuntos)
            );

            return true;
        } catch (Throwable $exception) {
            $this->registrarFallo($exception);

            return false;
        }
    }

    protected function registrarFallo(Throwable $exception): void
    {
        $motivo = $this->clasificarMotivo($exception->getMessage());

        DB::table('guardia_correos_fallidos')->insert([
            'guardia_id' => $this->guardia->id,
            'user_id'    => $this->usuario->id,
            'email'      => $this->usuario->email,
            'motivo'     => $motivo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function clasificarMotivo(string $mensaje): string
    {
        $mensajeLower = strtolower($mensaje);

        if (str_contains($mensajeLower, ['mailbox full', 'quota exceeded', 'mailbox unavailable', 'over quota', '552'])) {
            return '⚠️ Casilla llena (quota excedida)';
        }

        if (str_contains($mensajeLower, ['unauthenticated', 'authentication required', '535', '5.7.1'])) {
            return '❌ Error de autenticación SMTP';
        }

        if (str_contains($mensajeLower, ['connection', 'timeout', 'refused', 'timed out', '550'])) {
            return '❌ Error de conexión SMTP';
        }

        if (str_contains($mensajeLower, ['invalid address', 'syntax', '553'])) {
            return '❌ Dirección de correo inválida';
        }

        return '❓ ' . $mensaje;
    }
}