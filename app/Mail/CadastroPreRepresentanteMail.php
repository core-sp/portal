<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CadastroPreRepresentanteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function build()
    {
        return $this->subject('Cadastro no Pré-registro do Portal Core-SP')
            ->view('emails.default');
    }
}
