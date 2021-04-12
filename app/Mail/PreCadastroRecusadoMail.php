<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PreCadastroRecusadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct($motivo)
    {
        $this->body = '<strong>Requisição de pré-cadastro foi recusada pelo seguinte motivo:</strong>';
        $this->body  .= '<br>';
        $this->body  .= $motivo;
    }

    public function build()
    {
        return $this->subject('Requisição de pré-cadastro foi recusada')
            ->view('emails.default');
    }
}
