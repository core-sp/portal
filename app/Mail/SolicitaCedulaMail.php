<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SolicitaCedulaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct($cedula)
    {
        $this->body = 'Atualização de status da sua solicitação de cédula no Portal Core-SP.';
        $this->body .= '<br /><br />';
        $this->body .= '<strong>Código da solicitação:</strong> #'. $cedula->id;
        $this->body .= '<br /><br />';
        $this->body .= '<strong>Status:</strong> '. $cedula->status;
        $this->body .= '<br /><br />';
        $this->body .= 'Para verificá-la, acesse a <a href="' . route('representante.login') . '">área restrita do Representante Comercial</a> do Portal Core-SP.';
    }

    public function build()
    {
        return $this->subject('Atualização do status da solicitação de cédula')
            ->view('emails.default');
    }
}
