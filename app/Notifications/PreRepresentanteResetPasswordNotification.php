<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class PreRepresentanteResetPasswordNotification extends ResetPasswordNotification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Alteração de senha no Pré-registro')
            ->line('Você está recebendo este e-mail pois solicitou a reconfiguração de senha no Pré-registro do Portal do Core-SP.')
            ->line('Lembrando que este link é válido por 60 minutos.')
            ->action('Alteração de Senha no Pré-registro', route('prerepresentante.password.reset', $this->token))
            ->line('Caso não tenha solicitado a reconfiguração, favor desconsiderar este e-mail');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
