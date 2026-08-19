<?php

namespace App\Jobs;

use App\Models\Guard;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

/**
 * Envuelve el envío en lote de novedades de guardia por correo para
 * poder dispatchearse vía ->afterResponse() sin bloquear el request.
 *
 * IMPORTANTE: reemplaza a un dispatch(closure)->afterResponse() que
 * fallaba en producción. Un closure definido dentro de un método de
 * componente Livewire captura implícitamente $this (el componente,
 * una clase anónima), y eso NO es serializable — Laravel intenta
 * serializar el job igual con el driver 'sync', y tira
 * "Serialization of 'Livewire\Component@anonymous' is not allowed"
 * en la fase terminate() del kernel, DESPUÉS de que la respuesta ya
 * se mandó al navegador. Por eso el 18/08 el envío quedó roto en
 * silencio: sin error visible, sin correos salientes.
 *
 * Esta clase, al tener solo propiedades públicas de tipos
 * serializables (un modelo con SerializesModels + un array de ints +
 * strings), sí se serializa sin problema.
 */
class EnviarNovedadesGuardiaLoteJob
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * @param array<int> $usuarioIds IDs de los destinatarios (no la
     *   Collection de modelos, para mantener el payload liviano y
     *   evitar arrastrar relaciones cargadas).
     */
    public function __construct(
        public Guard $guardia,
        public array $usuarioIds,
        public string $nombreRemitente,
        public bool $incluirAdjuntos,
        public ?string $pdfContent,
        public bool $enviarZip,
        public ?string $zipContent,
    ) {}

    public function handle(): void
    {
        set_time_limit(120);

        $usuarios = User::whereIn('id', $this->usuarioIds)->get();

        foreach ($usuarios as $usuario) {
            EnviarNovedadGuardiaMail::dispatchSync(
                $this->guardia,
                $usuario,
                $this->nombreRemitente,
                $this->incluirAdjuntos,
                $this->pdfContent,
                $this->enviarZip,
                $this->zipContent,
            );
        }
    }
}