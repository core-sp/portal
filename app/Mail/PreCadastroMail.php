<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PreCadastroMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct()
    {
        $this->body = "<strong>Sua requisição de pré-cadastro foi submetida com sucesso!</strong>";
        $this->body   .= "<br>";
        $this->body   .= "Análise será realizada em 10 dias e resultado retornado por e-mail.";
    }

    public function build()
    {
        return $this->subject('Requisição de pré-cadastro foi solicitada')
            ->view('emails.default');
    }
}
