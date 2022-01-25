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

    private function emailReset($token)
    {
        $body = 'Você está recebendo este email pois solicitou alteração de senha na área restrita do Pré-Registro no Portal Core-SP.';
        $body .= '<br>';
        $body .= 'Lembrando que este link é válido por 60 minutos.';
        $body .= '<br>';
        $body .= 'Clique no link abaixo para continuar o procedimento.';
        $body .= '<br><br>';
        $body .= '<a href="'. route('prerepresentante.password.reset', $token) .'">Alterar senha</a>';
        $body .= '<br><br>';
        $body .= 'Caso não tenha solicitado, favor desconsiderar este email.';
        $body .= '<br><br>';
        $body .= 'Atenciosamente,';
        $body .= '<br>';
        $body .= 'Portal Core-SP';
    
        return $body;
    }

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
            ->view('emails.default', ['body' => $this->emailReset($this->token)]);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
