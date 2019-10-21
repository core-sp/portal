<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RepresentanteResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $body;

    public function __construct($token, $body)
    {
        $this->token = $token;
        $this->body = $body;
    }

    public function build()
    {
        return $this
            ->view('emails.default')
            ->subject('Alteração de senha');
    }
}
