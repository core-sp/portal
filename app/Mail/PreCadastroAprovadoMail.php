<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PreCadastroAprovadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct()
    {
        $this->body = '<strong>Requisição de pré-cadastro foi aprovada</strong>';
    }

    public function build()
    {
        return $this->subject('Requisição de pré-cadastro foi aprovada')
            ->view('emails.default');
    }
}
