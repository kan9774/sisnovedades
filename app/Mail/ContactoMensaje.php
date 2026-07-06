<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactoMensaje extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nombreContacto,
        public string $emailContacto,
        public string $mensajeContacto,
    ) {}

    public function build()
    {
        return $this->subject('Nuevo mensaje de contacto - ' . config('app.name'))
            ->replyTo($this->emailContacto, $this->nombreContacto)
            ->view('emails.contacto');
    }
}