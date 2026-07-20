<?php

namespace App\Livewire\Landing;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Mail\SugerenciaMail;
use Illuminate\Support\Facades\Mail;

class Sugerencias extends Component
{
    use WithFileUploads;

    public $nombre = '';
    public $email = '';
    public $rol = '';
    public $prioridad = 'baja';
    public $tipo = 'mejora';
    public $mensaje = '';
    public $adjunto = null;
    public $aceptar = false;
    public $enviado = false;
    public $error = null;

    protected $rules = [
        'nombre' => 'required|min:3|max:100',
        'email' => 'required|email|max:255',
        'rol' => 'required',
        'prioridad' => 'required',
        'tipo' => 'required',
        'mensaje' => 'required|min:10|max:5000',
        'adjunto' => 'nullable|file|max:5120|mimes:txt,pdf,doc,docx,jpg,jpeg,png,gif',
        'aceptar' => 'accepted',
    ];

    protected $messages = [
        'adjunto.max' => 'El archivo no puede superar los 5MB.',
        'adjunto.mimes' => 'Formato de archivo no permitido.',
        'aceptar.accepted' => 'Debés aceptar el uso de tus datos para poder enviar la sugerencia.',
    ];

    public function enviar()
    {
        $this->error = null;
        $this->validate();

        $adjuntoPath = null;
        $adjuntoNombreOriginal = null;

        if ($this->adjunto) {
            $adjuntoPath = $this->adjunto->store('sugerencias', 'public');
            $adjuntoNombreOriginal = $this->adjunto->getClientOriginalName();
        }

        $data = [
            'nombre' => $this->nombre,
            'email' => $this->email,
            'rol' => $this->rol,
            'prioridad' => $this->prioridad,
            'tipo' => $this->tipo,
            'mensaje' => $this->mensaje,
            'fecha' => now()->format('d/m/Y H:i'),
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ];

        // Guardar en base de datos (opcional)
        // Sugerencia::create($data + ['adjunto_path' => $adjuntoPath]);

        try {
            Mail::to(config('mail.sugerencias_to', 'guardiabcom1@gmail.com'))
                ->send(new SugerenciaMail($data, $adjuntoPath, $adjuntoNombreOriginal));
        } catch (\Throwable $e) {
            report($e);
            $this->error = 'No se pudo enviar la sugerencia. Por favor intentá nuevamente más tarde.';

            return;
        }

        $this->resetFormulario();
        $this->enviado = true;

        $this->dispatch('enviado', time());
    }

    public function resetFormulario()
    {
        $this->error = null;
        $this->nombre = '';
        $this->email = '';
        $this->rol = '';
        $this->prioridad = 'baja';
        $this->tipo = 'mejora';
        $this->mensaje = '';
        $this->aceptar = false;
        $this->adjunto = null;
    }

    public function render()
    {
        return view('livewire.landing.sugerencias.sugerencias');
    }
}