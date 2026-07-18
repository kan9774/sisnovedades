<?php

namespace App\Mail;

use App\Models\Guard;
use App\Support\GuardiaPdfGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuardiaNovedadesMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Guard $guardia,
        public string $remitenteName,
        public bool $incluirAdjuntos = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novedades de guardia del ' . $this->guardia->date->format('d/m/Y'),
        );
    }

    public function content(): Content
    {
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
        if ($this->incluirAdjuntos) {
            $contenidoPdf = GuardiaPdfGenerator::generarConAdjuntos($this->guardia);

            return [
                \Illuminate\Mail\Mailables\Attachment::fromData(
                    fn () => $contenidoPdf,
                    GuardiaPdfGenerator::nombreArchivoConAdjuntos($this->guardia),
                )->withMime('application/pdf'),
            ];
        }

        $pdf = GuardiaPdfGenerator::generar($this->guardia);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                GuardiaPdfGenerator::nombreArchivo($this->guardia),
            )->withMime('application/pdf'),
        ];
    }
}