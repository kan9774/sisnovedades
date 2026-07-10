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
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novedades de guardia del ' . $this->guardia->date->format('d/m/Y'),
        );
    }

    public function content(): Content
    {
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
        $pdf = GuardiaPdfGenerator::generar($this->guardia);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                GuardiaPdfGenerator::nombreArchivo($this->guardia),
            )->withMime('application/pdf'),
        ];
    }
}