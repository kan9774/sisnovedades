<?php

namespace App\Livewire\Landing;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Mail\ContactoMail;
use App\Mail\SugerenciaMail;
use Illuminate\Support\Facades\Mail;

class Contacto extends Component
{
    use WithFileUploads;

    public $nombre = '';
    public $email = '';
    public $mensaje = '';
    public $enviado = false;
    public $error = null;

    // Campos para sugerencias
    public $sugerencia_nombre = '';
    public $sugerencia_email = '';
    public $sugerencia_rol = '';
    public $sugerencia_prioridad = 'baja';
    public $sugerencia_tipo = 'mejora';
    public $sugerencia_mensaje = '';
    public $sugerencia_adjunto = null;
    public $sugerencia_aceptar = false;
    public $sugerencia_enviada = false;
    public $sugerencia_error = null;

    protected $messages = [
        'sugerencia_adjunto.max' => 'El archivo no puede superar los 5MB.',
        'sugerencia_adjunto.mimes' => 'Formato de archivo no permitido.',
        'sugerencia_aceptar.accepted' => 'Debés aceptar el uso de tus datos para poder enviar la sugerencia.',
    ];

    public function enviar()
    {
        $this->error = null;

        $this->validate([
            'nombre' => 'required|min:3|max:100',
            'email' => 'required|email|max:255',
            'mensaje' => 'required|min:10|max:5000',
        ]);

        $data = [
            'nombre' => $this->nombre,
            'email' => $this->email,
            'mensaje' => $this->mensaje,
            'fecha' => now()->format('d/m/Y H:i'),
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ];

        try {
            Mail::to(config('mail.contacto_to', 'admin@sisnovedades.com'))
                ->send(new ContactoMail($data));
        } catch (\Throwable $e) {
            report($e);
            $this->error = 'No se pudo enviar el mensaje. Por favor intentá nuevamente más tarde.';

            return;
        }

        $this->resetContacto();
        $this->enviado = true;
    }

    public function enviarSugerencia()
    {
        $this->sugerencia_error = null;

        $this->validate([
            'sugerencia_nombre' => 'required|min:3|max:100',
            'sugerencia_email' => 'required|email|max:255',
            'sugerencia_rol' => 'required',
            'sugerencia_prioridad' => 'required',
            'sugerencia_tipo' => 'required',
            'sugerencia_mensaje' => 'required|min:10|max:5000',
            'sugerencia_adjunto' => 'nullable|file|max:5120|mimes:txt,pdf,doc,docx,jpg,jpeg,png,gif',
            'sugerencia_aceptar' => 'accepted',
        ]);

        $adjuntoPath = null;
        $adjuntoNombreOriginal = null;

        if ($this->sugerencia_adjunto) {
            $adjuntoPath = $this->sugerencia_adjunto->store('sugerencias', 'public');
            $adjuntoNombreOriginal = $this->sugerencia_adjunto->getClientOriginalName();
        }

        $data = [
            'nombre' => $this->sugerencia_nombre,
            'email' => $this->sugerencia_email,
            'rol' => $this->sugerencia_rol,
            'prioridad' => $this->sugerencia_prioridad,
            'tipo' => $this->sugerencia_tipo,
            'mensaje' => $this->sugerencia_mensaje,
            'fecha' => now()->format('d/m/Y H:i'),
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ];

        // Guardar en base de datos (opcional)
        // Sugerencia::create($data + ['adjunto_path' => $adjuntoPath]);

        try {
            Mail::to(config('mail.sugerencias_to', 'admin@sisnovedades.com'))
                ->send(new SugerenciaMail($data, $adjuntoPath, $adjuntoNombreOriginal));
        } catch (\Throwable $e) {
            report($e);
            $this->sugerencia_error = 'No se pudo enviar la sugerencia. Por favor intentá nuevamente más tarde.';

            return;
        }

        $this->resetSugerencia();
        $this->sugerencia_enviada = true;
    }

    public function resetContacto()
    {
        $this->error = null;
        $this->nombre = '';
        $this->email = '';
        $this->mensaje = '';
    }

    public function resetSugerencia()
    {
        $this->sugerencia_error = null;
        $this->sugerencia_nombre = '';
        $this->sugerencia_email = '';
        $this->sugerencia_rol = '';
        $this->sugerencia_prioridad = 'baja';
        $this->sugerencia_tipo = 'mejora';
        $this->sugerencia_mensaje = '';
        $this->sugerencia_aceptar = false;
        $this->sugerencia_adjunto = null;
    }

    public function render()
    {
        return view('livewire.landing.contacto');
    }
}