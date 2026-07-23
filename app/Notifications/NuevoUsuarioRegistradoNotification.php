<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NuevoUsuarioRegistradoNotification extends Notification
{
    use Queueable;

    public function __construct(protected User $usuarioRegistrado)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $usuario = $this->usuarioRegistrado;

        return (new MailMessage)
            ->subject('Nuevo registro en ' . config('app.name'))
            ->greeting('Se registró un nuevo visitante')
            ->line("Nombre: {$usuario->grade} {$usuario->name} {$usuario->last_name}")
            ->line("Email: {$usuario->email}")
            ->line('Unidad: ' . ($usuario->unidad?->nombre ?? '—'))
            ->line('Quedó registrado con el rol "visitante" por defecto.')
            ->action('Ir al panel de usuarios', route('admin.users.index'))
            ->line('Revisá si necesita un rol distinto o si se queda como visitante.');
    }
}