<?php

namespace App\Mail;

use App\Models\Guard;
use App\Support\GuardiaPdfGenerator;
use App\Support\GuardiaZipGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuardiaNovedadesMail extends Mailable
{
    use Queueable, SerializesModels;

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
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novedades de guardia del ' . $this->guardia->date->format('d/m/Y'),
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