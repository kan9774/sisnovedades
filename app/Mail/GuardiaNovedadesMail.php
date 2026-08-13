<?php

namespace App\Mail;

use App\Models\Guard;
use App\Support\GuardiaPdfGenerator;
use App\Support\GuardiaZipGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GuardiaNovedadesMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Message-ID propio, generado ACÁ (antes de enviar) en vez de confiar
     * en $sentMessage->getMessageId() después.
     *
     * Motivo: Postfix responde al DATA command con
     * "250 2.0.0 Ok: queued as <QUEUE_ID>", y Symfony Mailer
     * (SmtpTransport) parsea ese "queued as" y lo usa como el
     * "messageId definitivo" del SentMessage, pisando el Message-ID real
     * del header del correo. Si guardáramos $sentMessage->getMessageId()
     * (como se hacía antes), terminábamos guardando el Queue ID de
     * Postfix (ej. "B6BE28029B"), que nunca aparece en el DSN de rebote
     * — el DSN siempre trae el Message-ID real del header rfc822
     * original. Fijando este valor nosotros mismos vía headers() y
     * guardando el mismo string después de enviar (ver
     * EnviarNovedadGuardiaMail::handle()), garantizamos que
     * guardia_correos_enviados.message_id coincida exactamente con lo
     * que ProcesarRebotesCommand va a parsear del DSN.
     */
    public readonly string $messageId;

    /**
     * @param string|null $pdfContent Binario del PDF ya generado. En un
     *   envío masivo (mismo PDF para N destinatarios) se genera UNA sola
     *   vez afuera y se pasa acá para no repetir el render de DomPDF +
     *   la fusión de FPDI por cada destinatario. Si viene null, este
     *   Mailable lo genera él mismo (uso individual, fuera de un loop).
     * @param string|null $zipContent Binario del ZIP ya armado (PDF +
     *   adjuntos crudos), generado una sola vez afuera. Mutuamente
     *   excluyente con $incluirAdjuntos — si $enviarZip es true, este
     *   Mailable ignora $incluirAdjuntos/$pdfContent para el adjunto
     *   (solo usa $pdfContent para el nombre de archivo si hiciera falta).
     */
    public function __construct(
        public Guard $guardia,
        public string $remitenteName,
        public bool $incluirAdjuntos = false,
        public ?string $pdfContent = null,
        public bool $enviarZip = false,
        public ?string $zipContent = null,
    ) {
        // Formato estandar de un Message-ID: <token-unico@dominio>.
        // Usamos el host de APP_URL como dominio; el valor en si no
        // necesita resolver a nada, solo ser unico y consistente.
        $dominio = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $this->messageId = (string) Str::uuid() . '@' . $dominio;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novedades de guardia del ' . $this->guardia->date->format('d/m/Y'),
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            messageId: $this->messageId,
        );
    }

    public function content(): Content
    {
        if ($this->enviarZip) {
            return new Content(
                view: 'emails.guardia-novedades-zip',
                with: [
                    'guardia' => $this->guardia,
                    'remitenteName' => $this->remitenteName,
                    'nombreArchivo' => GuardiaZipGenerator::nombreArchivo($this->guardia),
                ],
            );
        }

        if ($this->incluirAdjuntos) {
            return new Content(
                view: 'emails.recibidos-novedades',
                with: [
                    'guardia' => $this->guardia,
                    'nombreRemitente' => $this->remitenteName,
                    'nombreArchivo' => GuardiaPdfGenerator::nombreArchivoConAdjuntos($this->guardia),
                ],
            );
        }

        return new Content(
            view: 'emails.guardia-novedades',
            with: [
                'guardia' => $this->guardia,
                'remitenteName' => $this->remitenteName,
            ],
        );
    }

    public function attachments(): array
    {
        if ($this->enviarZip) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromData(
                    fn () => $this->zipContent,
                    GuardiaZipGenerator::nombreArchivo($this->guardia),
                )->withMime('application/zip'),
            ];
        }

        $nombreArchivo = $this->incluirAdjuntos
            ? GuardiaPdfGenerator::nombreArchivoConAdjuntos($this->guardia)
            : GuardiaPdfGenerator::nombreArchivo($this->guardia);

        // Reusamos el PDF pasado desde afuera si vino; si no, lo generamos
        // acá (fallback para uso individual del Mailable fuera de un loop).
        $contenidoPdf = $this->pdfContent ?? (
            $this->incluirAdjuntos
                ? GuardiaPdfGenerator::generarConAdjuntos($this->guardia)
                : GuardiaPdfGenerator::generar($this->guardia)->output()
        );

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $contenidoPdf,
                $nombreArchivo,
            )->withMime('application/pdf'),
        ];
    }
}