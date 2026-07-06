<?php

namespace App\Livewire\Landing;

use App\Mail\ContactoMensaje;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class Contacto extends Component
{
    public string $nombre = '';
    public string $email = '';
    public string $mensaje = '';

    public bool $enviado = false;

    protected function rules(): array
    {
        return [
            'nombre'  => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'mensaje' => 'required|string|max:2000',
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required'  => 'Ingresá tu nombre.',
            'email.required'   => 'Ingresá tu email.',
            'email.email'      => 'Ingresá un email válido.',
            'mensaje.required' => 'Escribí tu consulta.',
        ];
    }

    public function enviar(): void
    {
        $this->validate();

        Mail::to('comunicaciones@ejercito.mil.ar')
            ->send(new ContactoMensaje($this->nombre, $this->email, $this->mensaje));

        $this->reset(['nombre', 'email', 'mensaje']);
        $this->enviado = true;
    }

    public function render()
    {
        return view('livewire.landing.contacto');
    }
}