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
    ) {}

    public function handle(): bool
    {
        try {
            Mail::to($this->usuario->email)->send(
                new GuardiaNovedadesMail($this->guardia, $this->nombreRemitente)
            );

            return true;
        } catch (Throwable $exception) {
            $this->registrarFallo($exception);

            return false;
        }
    }

    protected function registrarFallo(Throwable $exception): void
    {
        DB::table('guardia_correos_fallidos')->insert([
            'guardia_id' => $this->guardia->id,
            'user_id'    => $this->usuario->id,
            'email'      => $this->usuario->email,
            'motivo'     => $exception->getMessage(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}