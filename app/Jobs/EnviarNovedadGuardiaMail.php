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
            $mailable = new GuardiaNovedadesMail(
                $this->guardia,
                $this->nombreRemitente,
                $this->incluirAdjuntos,
                $this->pdfContent,
                $this->enviarZip,
                $this->zipContent,
            );

            Mail::to($this->usuario->email)->send($mailable);

            // IMPORTANTE: usamos $mailable->messageId (el que nosotros
            // generamos y fijamos vía GuardiaNovedadesMail::headers()),
            // NO $sentMessage->getMessageId().
            //
            // Symfony Mailer / SmtpTransport busca en la respuesta SMTP
            // el patrón "250 Ok: queued as <ID>" y, si lo encuentra, lo
            // usa como el "messageId definitivo" del SentMessage. Postfix
            // responde justo así, así que getMessageId() terminaba
            // devolviendo el Queue ID interno de Postfix (ej.
            // "B6BE28029B") en vez del Message-ID real del header del
            // correo — y ese Queue ID nunca aparece en el DSN de rebote,
            // rompiendo la correlación en silencio (guardaba algo, pero
            // no lo que el rebote iba a traer).
            //
            // $mailable->messageId es el mismo valor que quedó en el
            // header "Message-ID:" del correo realmente enviado, así que
            // es el mismo que ProcesarRebotesCommand va a extraer del
            // DSN cuando rebote.
            $messageId = $mailable->messageId;

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