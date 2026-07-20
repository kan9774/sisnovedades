<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SugerenciaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data,
        public ?string $adjuntoPath = null,
        public ?string $adjuntoNombreOriginal = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nueva sugerencia: {$this->data['tipo']} - {$this->data['prioridad']}",
            replyTo: [$this->data['email']],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sugerencia',
        );
    }

    public function attachments(): array
    {
        if (! $this->adjuntoPath) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('public', $this->adjuntoPath)
                ->as($this->adjuntoNombreOriginal ?? basename($this->adjuntoPath)),
        ];
    }
}