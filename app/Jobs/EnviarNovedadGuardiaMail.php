<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Guard;
use App\Mail\GuardiaNovedadesMail;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

    /**
     * @param string|null $pdfContent Binario del PDF ya generado (una
     *   sola vez) desde afuera, para no regenerarlo en cada uno de los
     *   N destinatarios de un mismo envío.
     * @param string|null $zipContent Binario del ZIP ya armado (PDF +
     *   adjuntos crudos), generado una sola vez afuera. Mutuamente
     *   excluyente con $incluirAdjuntos.
     */
    public function __construct(
        public Guard $guardia,
        public User $usuario,
        public string $nombreRemitente,
        public bool $incluirAdjuntos = false,
        public ?string $pdfContent = null,
        public bool $enviarZip = false,
        public ?string $zipContent = null,
    ) {}

    public function handle(): bool
    {
        try {
            $sentMessage = Mail::to($this->usuario->email)->send(
                new GuardiaNovedadesMail(
                    $this->guardia,
                    $this->nombreRemitente,
                    $this->incluirAdjuntos,
                    $this->pdfContent,
                    $this->enviarZip,
                    $this->zipContent,
                )
            );

            // Laravel 13 (Symfony Mailer) devuelve el SentMessage con el
            // Message-ID real, que se usa para correlacionar rebotes
            // diferidos (DSN) leídos después por mail:procesar-rebotes.
            //
            // IMPORTANTE: Symfony devuelve el Message-ID CON los signos
            // <...>, pero el regex que parsea el DSN en
            // ProcesarRebotesCommand extrae el valor SIN esos signos
            // (Message-ID:\s*<([^>]+)>). Si acá se guarda con < > nunca
            // va a matchear contra lo que llega parseado del rebote, y la
            // correlación falla siempre en silencio. Se normaliza sin
            // brackets para que ambos lados queden consistentes.
            $messageId = trim($sentMessage?->getMessageId() ?? '', '<> ');

            if ($messageId) {
                DB::table('guardia_correos_enviados')->insert([
                    'guardia_id' => $this->guardia->id,
                    'user_id'    => $this->usuario->id,
                    'email'      => $this->usuario->email,
                    'message_id' => $messageId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

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

        if (Str::contains($mensajeLower, ['mailbox full', 'quota exceeded', 'mailbox unavailable', 'over quota', '552'])) {
            return '⚠️ Casilla llena (quota excedida)';
        }

        if (Str::contains($mensajeLower, ['unauthenticated', 'authentication required', '535', '5.7.1'])) {
            return '❌ Error de autenticación SMTP';
        }

        if (Str::contains($mensajeLower, ['connection', 'timeout', 'refused', 'timed out', '550'])) {
            return '❌ Error de conexión SMTP';
        }

        if (Str::contains($mensajeLower, ['invalid address', 'syntax', '553'])) {
            return '❌ Dirección de correo inválida';
        }

        return '❓ ' . $mensaje;
    }
}